<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\ParishionerRequirement;
use App\Models\ServiceRequirement;
use App\Models\Service;
use App\Services\BookingEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequirementController extends Controller
{
    /**
     * "My Requirements" — all declarations grouped by service.
     */
    public function index()
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile')
                ->with('info', 'Please complete your profile first.');
        }

        // Auto-match existing sacramental records (safe to call every time)
        app(BookingEligibilityService::class)->autoMatchSacraments($parishioner);

        // Load all service requirements that have the gate types
        $services = Service::where('is_bookable', true)
            ->where('is_active', true)
            ->whereHas('serviceRequirements', fn($q) =>
                $q->where('is_active', true)
                  ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
            )
            ->orderBy('sort_order')
            ->with(['serviceRequirements' => fn($q) =>
                $q->where('is_active', true)
                  ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
                  ->orderBy('sort_order')
            ])
            ->get();

        // Load the parishioner's declarations keyed by service_requirement_id
        $declarations = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->get()
            ->keyBy('service_requirement_id');

        $isNew = $declarations->isEmpty()
            && !$parishioner->sacramentalRecords()->exists();

        return view('parishioner.my-requirements.index', compact(
            'services', 'declarations', 'parishioner', 'isNew'
        ));
    }

    /**
     * Show the declaration form for one requirement.
     */
    public function declare(ServiceRequirement $serviceRequirement)
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile');
        }

        // Prevent editing a verified declaration
        $existing = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $serviceRequirement->id)
            ->first();

        if ($existing && $existing->isVerified()) {
            return redirect()->route('parishioner.my-requirements.index')
                ->with('info', 'This requirement has already been verified and cannot be edited.');
        }

        $service = $serviceRequirement->service;

        return view('parishioner.my-requirements.declare', compact(
            'serviceRequirement', 'existing', 'service'
        ));
    }

    /**
     * Submit or resubmit a declaration.
     */
    public function store(Request $request, ServiceRequirement $serviceRequirement)
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile');
        }

        // Block editing verified declarations
        $existing = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $serviceRequirement->id)
            ->first();

        if ($existing && $existing->isVerified()) {
            return back()->with('info', 'This requirement has already been verified.');
        }

        // Validate
        $rules = [
            'date_completed'      => ['required', 'date', 'before_or_equal:today'],
            'venue_or_organizer'  => ['required', 'string', 'max:255'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ];

        if ($serviceRequirement->requires_proof) {
            if ($existing && $existing->proof_path) {
                // Already has proof — only require new upload if submitting a new file
                $rules['proof'] = ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,docx'];
            } else {
                $rules['proof'] = ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,docx'];
            }
        }

        $validated = $request->validate($rules);

        // Handle proof upload
        $proofPath = $existing?->proof_path;
        if ($request->hasFile('proof')) {
            // Delete old proof if exists
            if ($proofPath) {
                try { Storage::disk('supabase')->delete($proofPath); } catch (\Exception $e) {}
            }
            $proofPath = $request->file('proof')->store(
                'parishioner-requirements/' . $parishioner->id,
                'supabase'
            );
        }

        // Create or update — reset to pending on resubmit
        ParishionerRequirement::updateOrCreate(
            [
                'parishioner_id'         => $parishioner->id,
                'service_requirement_id' => $serviceRequirement->id,
            ],
            [
                'status'              => 'pending',
                'date_completed'      => $validated['date_completed'],
                'venue_or_organizer'  => $validated['venue_or_organizer'],
                'notes'               => $validated['notes'] ?? null,
                'proof_path'          => $proofPath,
                'source'              => 'self_declared',
                'verified_by'         => null,
                'verified_at'         => null,
                'rejection_reason'    => null,
            ]
        );

        // Notify admins
        $this->notifyAdmins($parishioner, $serviceRequirement);

        return redirect()->route('parishioner.my-requirements.index')
            ->with('success', 'Your declaration for "' . $serviceRequirement->name . '" has been submitted. The parish office will verify it shortly.');
    }

    private function notifyAdmins($parishioner, $req): void
    {
        try {
            $admins = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminParishionerRequirementNotification(
                    $parishioner, $req
                ));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Requirement admin notify failed: ' . $e->getMessage());
        }
    }
}
