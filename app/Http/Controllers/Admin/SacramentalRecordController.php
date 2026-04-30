<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use Illuminate\Http\Request;

class SacramentalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = SacramentalRecord::with('parishioner');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('parishioner', fn($q) => $q->search($search));
        }

        if ($from = $request->get('date_from')) {
            $query->where('date_administered', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('date_administered', '<=', $to);
        }

        $records = $query->orderByDesc('date_administered')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sacramental-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $parishioner = $request->get('parishioner_id')
            ? Parishioner::find($request->get('parishioner_id'))
            : null;

        return view('admin.sacramental-records.create', compact('parishioner'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecord($request);
        $validated['recorded_by'] = auth()->id();

        $record = SacramentalRecord::create($validated);

        AuditLog::record('create', $record, [], $record->toArray(), "Sacramental record ({$record->type}) created");

        // Auto-generate certificate if applicable
        if (in_array($record->type, ['baptism', 'confirmation', 'marriage', 'first_communion'])) {
            app(\App\Services\CertificateService::class)->autoGenerate($record);
        }

        return redirect()->route('admin.sacramental-records.show', $record)
            ->with('success', 'Sacramental record created successfully.');
    }

    public function show(SacramentalRecord $sacramentalRecord)
    {
        $sacramentalRecord->load(['parishioner', 'spouseParishioner', 'recordedBy', 'verifiedBy', 'certificate']);
        return view('admin.sacramental-records.show', compact('sacramentalRecord'));
    }

    public function edit(SacramentalRecord $sacramentalRecord)
    {
        return view('admin.sacramental-records.edit', compact('sacramentalRecord'));
    }

    public function update(Request $request, SacramentalRecord $sacramentalRecord)
    {
        $validated = $this->validateRecord($request);
        $oldValues = $sacramentalRecord->toArray();

        $sacramentalRecord->update($validated);

        AuditLog::record('update', $sacramentalRecord, $oldValues, $sacramentalRecord->fresh()->toArray(), 'Sacramental record updated');

        return redirect()->route('admin.sacramental-records.show', $sacramentalRecord)
            ->with('success', 'Record updated successfully.');
    }

    public function destroy(SacramentalRecord $sacramentalRecord)
    {
        AuditLog::record('delete', $sacramentalRecord, $sacramentalRecord->toArray(), [], 'Sacramental record deleted');
        $sacramentalRecord->delete();

        return redirect()->route('admin.sacramental-records.index')
            ->with('success', 'Record deleted.');
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $term = $request->get('q', '');
        $results = SacramentalRecord::with('parishioner')
            ->whereHas('parishioner', fn($q) => $q->search($term))
            ->orWhere('register_number', 'like', "%{$term}%")
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id'   => $r->id,
                'text' => ucfirst(str_replace('_', ' ', $r->type)) . ' — ' . $r->parishioner->full_name,
                'meta' => $r->date_administered->format('M d, Y')
                    . ($r->register_number ? ' · Reg# ' . $r->register_number : '')
                    . ' · ID #' . $r->id,
            ]);

        return response()->json($results);
    }

    public function verify(SacramentalRecord $sacramentalRecord)
    {
        $sacramentalRecord->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        AuditLog::record('verify', $sacramentalRecord, [], [], 'Sacramental record verified');

        return back()->with('success', 'Record verified.');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'parishioner_id'        => ['required', 'exists:parishioners,id'],
            'spouse_parishioner_id' => ['nullable', 'exists:parishioners,id'],
            'type'                  => ['required', 'in:baptism,first_communion,confirmation,marriage,death_burial'],
            'date_administered'     => ['required', 'date'],
            'celebrant'             => ['required', 'string', 'max:255'],
            'venue'                 => ['nullable', 'string', 'max:255'],
            'register_number'       => ['nullable', 'string', 'max:50'],
            'page_number'           => ['nullable', 'string', 'max:20'],
            'line_number'           => ['nullable', 'string', 'max:20'],
            'godparents'            => ['nullable', 'array'],
            'godparents.*'          => ['string', 'max:255'],
            'witnesses'             => ['nullable', 'array'],
            'witnesses.*'           => ['string', 'max:255'],
            'sponsors'              => ['nullable', 'array'],
            'sponsors.*'            => ['string', 'max:255'],
            'document_references'   => ['nullable', 'array'],
            'notes'                 => ['nullable', 'string'],
        ]);
    }
}
