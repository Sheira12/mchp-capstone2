<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Family;
use App\Models\Parishioner;
use App\Models\ProfileChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParishionerController extends Controller
{
    public function index(Request $request)
    {
        $query = Parishioner::with('family')->withCount('sacramentalRecords');

        // Search
        if ($term = $request->get('search')) {
            $query->search($term);
        }

        // Filters
        if ($barangay = $request->get('barangay')) {
            $query->byBarangay($barangay);
        }

        if ($family = $request->get('family_id')) {
            $query->where('family_id', $family);
        }

        if ($sacrament = $request->get('sacrament')) {
            $query->whereHas('sacramentalRecords', fn($q) => $q->where('type', $sacrament));
        }

        if ($request->get('active_only')) {
            $query->where('is_active', true);
        }

        $parishioners = $query->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $barangays = Parishioner::distinct()->pluck('barangay')->filter()->sort()->values();
        $families  = Family::orderBy('family_name')->get(['id', 'family_name']);

        return view('admin.parishioners.index', compact('parishioners', 'barangays', 'families'));
    }

    public function create()
    {
        $families = Family::orderBy('family_name')->get(['id', 'family_name']);
        return view('admin.parishioners.create', compact('families'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateParishioner($request);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('parishioners/photos', 'public');
        }

        // Duplicate detection
        $duplicate = Parishioner::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->where('birthdate', $validated['birthdate'] ?? null)
            ->first();

        if ($duplicate && !$request->boolean('confirm_duplicate')) {
            return back()->withInput()->with('duplicate_warning', $duplicate);
        }

        $parishioner = Parishioner::create($validated);

        AuditLog::record('create', $parishioner, [], $parishioner->toArray(), 'Parishioner profile created');

        return redirect()->route('admin.parishioners.show', $parishioner)
            ->with('success', 'Parishioner profile created successfully.');
    }

    public function show(Parishioner $parishioner)
    {
        $parishioner->load([
            'family.members',
            'sacramentalRecords',
            'bookings' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->latest()->take(10),
            'certificates',
            'profileChanges' => fn($q) => $q->with('changedBy')->latest()->take(20),
        ]);

        return view('admin.parishioners.show', compact('parishioner'));
    }

    public function edit(Parishioner $parishioner)
    {
        $families = Family::orderBy('family_name')->get(['id', 'family_name']);
        return view('admin.parishioners.edit', compact('parishioner', 'families'));
    }

    public function update(Request $request, Parishioner $parishioner)
    {
        $validated = $this->validateParishioner($request, $parishioner->id);

        if ($request->hasFile('photo')) {
            if ($parishioner->photo_path) {
                Storage::disk('public')->delete($parishioner->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('parishioners/photos', 'public');
        }

        // Log changes
        $oldValues = $parishioner->toArray();
        foreach ($validated as $field => $newValue) {
            $oldValue = $parishioner->$field;
            if ($oldValue != $newValue) {
                ProfileChangeLog::create([
                    'parishioner_id' => $parishioner->id,
                    'changed_by'     => auth()->id(),
                    'field_name'     => $field,
                    'old_value'      => $oldValue,
                    'new_value'      => $newValue,
                    'reason'         => $request->get('change_reason'),
                ]);
            }
        }

        $parishioner->update($validated);

        AuditLog::record('update', $parishioner, $oldValues, $parishioner->fresh()->toArray(), 'Parishioner profile updated');

        return redirect()->route('admin.parishioners.show', $parishioner)
            ->with('success', 'Parishioner profile updated successfully.');
    }

    public function destroy(Parishioner $parishioner)
    {
        AuditLog::record('delete', $parishioner, $parishioner->toArray(), [], 'Parishioner profile deleted');
        $parishioner->delete();

        return redirect()->route('admin.parishioners.index')
            ->with('success', 'Parishioner profile deleted.');
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $results = Parishioner::search($term)
            ->select('id', 'first_name', 'middle_name', 'last_name', 'birthdate', 'contact_number')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'    => $p->id,
                'text'  => $p->full_name,
                'extra' => $p->contact_number,
            ]);

        return response()->json($results);
    }

    private function validateParishioner(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'family_id'           => ['nullable', 'exists:families,id'],
            'first_name'          => ['required', 'string', 'max:100'],
            'middle_name'         => ['nullable', 'string', 'max:100'],
            'last_name'           => ['required', 'string', 'max:100'],
            'suffix'              => ['nullable', 'string', 'max:20'],
            'birthdate'           => ['nullable', 'date', 'before:today'],
            'gender'              => ['nullable', 'in:male,female,other'],
            'civil_status'        => ['nullable', 'in:single,married,widowed,separated,annulled'],
            'address'             => ['nullable', 'string', 'max:255'],
            'barangay'            => ['nullable', 'string', 'max:100'],
            'city'                => ['nullable', 'string', 'max:100'],
            'province'            => ['nullable', 'string', 'max:100'],
            'postal_code'         => ['nullable', 'string', 'max:10'],
            'contact_number'      => ['nullable', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:255'],
            'photo'               => ['nullable', 'image', 'max:2048'],
            'is_head_of_family'   => ['boolean'],
            'relationship_to_head' => ['nullable', 'string', 'max:100'],
            'is_active'           => ['boolean'],
            'notes'               => ['nullable', 'string'],
        ]);
    }
}
