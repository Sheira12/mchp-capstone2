<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $query = Family::withCount('members');

        if ($search = $request->get('search')) {
            $query->where('family_name', 'like', "%{$search}%")
                  ->orWhere('barangay', 'like', "%{$search}%");
        }

        $families = $query->orderBy('family_name')->paginate(20)->withQueryString();

        return view('admin.families.index', compact('families'));
    }

    public function create()
    {
        return view('admin.families.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_name'    => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'province'       => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'notes'          => ['nullable', 'string'],
        ]);

        $family = Family::create($validated);

        return redirect()->route('admin.families.show', $family)
            ->with('success', 'Family created successfully.');
    }

    public function show(Family $family)
    {
        $family->load(['members' => fn($q) => $q->orderBy('is_head_of_family', 'desc')->orderBy('last_name')]);
        return view('admin.families.show', compact('family'));
    }

    public function edit(Family $family)
    {
        return view('admin.families.edit', compact('family'));
    }

    public function update(Request $request, Family $family)
    {
        $validated = $request->validate([
            'family_name'    => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'province'       => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'notes'          => ['nullable', 'string'],
        ]);

        $family->update($validated);

        return redirect()->route('admin.families.show', $family)
            ->with('success', 'Family updated.');
    }

    public function search(Request $request)
    {
        $term = $request->get('q', '');
        if (strlen($term) < 2) return response()->json([]);

        $results = Family::where('family_name', 'like', "%{$term}%")
            ->orWhere('barangay', 'like', "%{$term}%")
            ->orWhere('contact_number', 'like', "%{$term}%")
            ->withCount('members')
            ->limit(10)
            ->get()
            ->map(fn($f) => [
                'id'    => $f->id,
                'text'  => $f->family_name,
                'extra' => $f->barangay ? $f->barangay . ' · ' . $f->members_count . ' members' : $f->members_count . ' members',
                'url'   => route('admin.families.show', $f->id),
            ]);

        return response()->json($results);
    }

    public function destroy(Family $family)
    {
        $family->delete();
        return redirect()->route('admin.families.index')->with('success', 'Family deleted.');
    }
}
