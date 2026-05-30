<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Parishioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user        = auth()->user();
        $parishioner = $user->parishioner;

        return view('parishioner.profile', compact('user', 'parishioner'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:100'],
            'middle_name'    => ['nullable', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'suffix'         => ['nullable', 'string', 'max:20'],
            'birthdate'      => ['nullable', 'date', 'before:today'],
            'gender'         => ['nullable', 'in:male,female,other'],
            'civil_status'   => ['nullable', 'in:single,married,widowed,separated,annulled'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'city'           => ['nullable', 'string', 'max:100'],
            'province'       => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:10'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'photo'          => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle photo upload separately (not part of fillable data)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            if ($user->parishioner?->photo_path) {
                Storage::disk('public')->delete($user->parishioner->photo_path);
            }
            $photoPath = $request->file('photo')->store('parishioners/photos', 'public');
        }

        // Remove 'photo' key — it's not a DB column
        unset($validated['photo']);

        if ($photoPath) {
            $validated['photo_path'] = $photoPath;
        }

        if ($user->parishioner) {
            $user->parishioner->update($validated);
        } else {
            // Create new parishioner profile and link to user
            $parishioner = Parishioner::create(array_merge($validated, [
                'email'     => $user->email,
                'is_active' => true,
            ]));
            $user->parishioner_id = $parishioner->id;
            $user->save();
        }

        // ── Sync the User.name field so the sidebar/topbar shows the updated name ──
        $fullName = trim(
            $validated['first_name'] . ' ' .
            ($validated['middle_name'] ? substr($validated['middle_name'], 0, 1) . '. ' : '') .
            $validated['last_name'] .
            ($validated['suffix'] ? ' ' . $validated['suffix'] : '')
        );
        $user->update(['name' => $fullName]);

        // Log profile change (one entry summarising the update)
        if ($user->parishioner_id) {
            \App\Models\ProfileChangeLog::create([
                'parishioner_id' => $user->parishioner_id,
                'changed_by'     => $user->id,
                'field_name'     => 'profile_update',
                'new_value'      => 'Self-service profile update',
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
