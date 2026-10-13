<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\EligibilityService;

class EligibilityController extends Controller
{
    public function __construct(private EligibilityService $eligibility) {}

    /**
     * Show "My Eligibility" — per-service checklist for the logged-in parishioner.
     */
    public function index()
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')
                ->with('info', 'Please complete your profile first.');
        }

        // Only show services that have eligibility rules
        $services = Service::where('is_bookable', true)
            ->where('is_active', true)
            ->whereHas('eligibilityRules')
            ->orderBy('sort_order')
            ->get();

        $results = $services->map(function (Service $svc) use ($parishioner) {
            try {
                return [
                    'service' => $svc,
                    'result'  => $this->eligibility->check($parishioner, $svc->slug),
                ];
            } catch (\Exception $e) {
                return null;
            }
        })->filter()->values();

        return view('parishioner.eligibility.index', compact('results', 'parishioner'));
    }

    /**
     * Check eligibility for a specific service — JSON endpoint for the booking form.
     * Uses BookingEligibilityService (v2) which checks parishioner_requirements.
     */
    public function check(string $slug)
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return response()->json(['eligible' => false, 'error' => 'No parishioner profile.'], 422);
        }

        $service = \App\Models\Service::where('slug', $slug)->first();
        if (!$service) {
            return response()->json(['eligible' => true, 'items' => [], 'progress' => ['satisfied'=>0,'total'=>0,'pct'=>100]]);
        }

        try {
            // Auto-match sacramental records so any existing records are satisfied immediately
            app(\App\Services\BookingEligibilityService::class)->autoMatchSacraments($parishioner);

            $result = app(\App\Services\BookingEligibilityService::class)->check($parishioner, $slug);
            return response()->json($result->toArray());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('BookingEligibilityService check failed: ' . $e->getMessage());
            return response()->json(['eligible' => true, 'items' => [], 'progress' => ['satisfied'=>0,'total'=>0,'pct'=>100]]);
        }
    }
}
