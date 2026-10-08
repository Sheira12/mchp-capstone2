<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Parishioner;
use Illuminate\Http\Request;

/**
 * Parishioner-facing family management.
 * A parishioner can create a family record, set themselves as head,
 * and add/remove other parishioner members under their family.
 */
class FamilyController extends Controller
{
    private function myParishioner(): ?Parishioner
    {
        return auth()->user()->parishioner;
    }

    /**
     * Show the current parishioner's family, or a form to create one.
     */
    public function index()
    {
        $parishioner = $this->myParishioner();

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')
                ->with('info', 'Please complete your profile before managing your family.');
        }

        $family = $parishioner->family;

        return view('parishioner.family.index', compact('parishioner', 'family'));
    }

    /**
     * Create a new family and set the current parishioner as head.
     */
    public function store(Request $request)
    {
        $parishioner = $this->myParishioner();
        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        if ($parishioner->family_id) {
            return back()->with('error', 'You are already in a family. Leave your current family first if you want to create a new one.');
        }

        $validated = $request->validate([
            'family_name'    => ['required', 'string', 'max:255'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'province'       => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        $family = Family::create($validated);

        // Link the parishioner as head
        $parishioner->update([
            'family_id'           => $family->id,
            'is_head_of_family'   => true,
            'relationship_to_head'=> 'Head',
        ]);

        return redirect()->route('parishioner.family.index')
            ->with('success', 'Family "' . $family->family_name . '" created. You are the head of this family.');
    }

    /**
     * Add a family member by parishioner search (name + birthdate duplicate detection).
     * The parishioner must already have an account or the admin must create their profile.
     * Here we allow linking an existing parishioner to this family.
     */
    public function addMember(Request $request)
    {
        $parishioner = $this->myParishioner();
        if (!$parishioner || !$parishioner->family_id || !$parishioner->is_head_of_family) {
            return back()->with('error', 'Only the head of the family can add members.');
        }

        $validated = $request->validate([
            'first_name'           => ['required', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'middle_name'          => ['nullable', 'string', 'max:100'],
            'birthdate'            => ['nullable', 'date', 'before:today'],
            'gender'               => ['nullable', 'in:male,female,other'],
            'civil_status'         => ['nullable', 'in:single,married,widowed,separated,annulled'],
            'relationship_to_head' => ['required', 'string', 'max:100'],
            'contact_number'       => ['nullable', 'string', 'max:20'],
        ]);

        // Duplicate detection: warn if name+birthdate already exists
        $duplicate = Parishioner::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->when($validated['birthdate'] ?? null, fn($q, $bd) => $q->where('birthdate', $bd))
            ->first();

        if ($duplicate && $duplicate->id !== $parishioner->id) {
            if ($duplicate->family_id && $duplicate->family_id !== $parishioner->family_id) {
                return back()->withInput()->with('warning',
                    $duplicate->full_name . ' already belongs to another family. '
                    . 'If this is the same person, please contact the parish office.'
                );
            }
            // Same or no family — link them
            $duplicate->update([
                'family_id'            => $parishioner->family_id,
                'relationship_to_head' => $validated['relationship_to_head'],
                'is_head_of_family'    => false,
            ]);
            return redirect()->route('parishioner.family.index')
                ->with('success', $duplicate->full_name . ' has been added to your family.');
        }

        // Create a new parishioner record linked to this family
        // (no user account — admin can create one later)
        $member = Parishioner::create([
            'first_name'           => $validated['first_name'],
            'middle_name'          => $validated['middle_name'] ?? null,
            'last_name'            => $validated['last_name'],
            'birthdate'            => $validated['birthdate'] ?? null,
            'gender'               => $validated['gender'] ?? null,
            'civil_status'         => $validated['civil_status'] ?? null,
            'contact_number'       => $validated['contact_number'] ?? null,
            'family_id'            => $parishioner->family_id,
            'is_head_of_family'    => false,
            'relationship_to_head' => $validated['relationship_to_head'],
            'is_active'            => true,
            // Inherit family address
            'address'   => $parishioner->family->address,
            'barangay'  => $parishioner->family->barangay,
            'city'      => $parishioner->family->city,
            'province'  => $parishioner->family->province,
        ]);

        return redirect()->route('parishioner.family.index')
            ->with('success', $member->full_name . ' has been added to your family.');
    }

    /**
     * Remove a member from the family (only the head can do this,
     * and the head cannot remove themselves).
     */
    public function removeMember(Parishioner $member)
    {
        $parishioner = $this->myParishioner();
        if (!$parishioner || !$parishioner->family_id || !$parishioner->is_head_of_family) {
            return back()->with('error', 'Only the head of the family can remove members.');
        }

        if ($member->id === $parishioner->id) {
            return back()->with('error', 'You cannot remove yourself. Delete the family instead.');
        }

        if ($member->family_id !== $parishioner->family_id) {
            abort(403);
        }

        $member->update([
            'family_id'            => null,
            'is_head_of_family'    => false,
            'relationship_to_head' => null,
        ]);

        return back()->with('success', $member->full_name . ' has been removed from your family.');
    }
}
