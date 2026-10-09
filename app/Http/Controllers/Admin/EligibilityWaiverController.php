<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EligibilityRule;
use App\Models\EligibilityWaiver;
use App\Models\Parishioner;
use App\Services\EligibilityService;
use Illuminate\Http\Request;

class EligibilityWaiverController extends Controller
{
    public function __construct()
    {
        // Only super_admin may grant waivers
        $this->middleware('role:super_admin');
    }

    /** Index: all waivers, filterable. */
    public function index(Request $request)
    {
        $query = EligibilityWaiver::with(['rule.service', 'parishioner', 'waivedBy', 'booking'])
            ->orderByDesc('waived_at');

        if ($search = $request->get('search')) {
            $query->whereHas('parishioner', fn($q) => $q->search($search));
        }

        $waivers = $query->paginate(20)->withQueryString();

        return view('admin.eligibility-waivers.index', compact('waivers'));
    }

    /**
     * Show waiver grant form for a specific parishioner + rule.
     * Linked from the eligibility overview on the parishioner detail page.
     */
    public function create(Request $request)
    {
        $parishioner = Parishioner::findOrFail($request->get('parishioner_id'));
        $rule        = EligibilityRule::with('service')->findOrFail($request->get('rule_id'));

        return view('admin.eligibility-waivers.create', compact('parishioner', 'rule'));
    }

    /** Grant the waiver. Immutable once created. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parishioner_id'     => ['required', 'exists:parishioners,id'],
            'eligibility_rule_id'=> ['required', 'exists:eligibility_rules,id'],
            'booking_id'         => ['nullable', 'exists:bookings,id'],
            'reason'             => ['required', 'string', 'min:10', 'max:1000'],
            'expires_at'         => ['nullable', 'date', 'after:now'],
        ]);

        // Prevent duplicate active waivers
        $existing = EligibilityWaiver::where('eligibility_rule_id', $validated['eligibility_rule_id'])
            ->where('parishioner_id', $validated['parishioner_id'])
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if ($existing) {
            return back()->withErrors(['reason' => 'An active waiver already exists for this rule and parishioner.']);
        }

        $waiver = EligibilityWaiver::create([
            'eligibility_rule_id'=> $validated['eligibility_rule_id'],
            'parishioner_id'     => $validated['parishioner_id'],
            'booking_id'         => $validated['booking_id'] ?? null,
            'waived_by'          => auth()->id(),
            'reason'             => $validated['reason'],
            'waived_at'          => now(),
            'expires_at'         => $validated['expires_at'] ?? null,
        ]);

        // Immutable audit log
        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'eligibility_waiver_granted',
            'model_type' => EligibilityWaiver::class,
            'model_id'   => $waiver->id,
            'new_values' => [
                'parishioner_id'      => $validated['parishioner_id'],
                'eligibility_rule_id' => $validated['eligibility_rule_id'],
                'reason'              => $validated['reason'],
                'expires_at'          => $validated['expires_at'] ?? null,
            ],
            'notes'      => 'Eligibility waiver granted by ' . auth()->user()->name,
            'ip_address' => $request->ip(),
        ]);

        // Re-check eligibility and notify if now fully eligible
        try {
            $parishioner = Parishioner::find($validated['parishioner_id']);
            $rule        = EligibilityRule::find($validated['eligibility_rule_id']);
            $result      = app(EligibilityService::class)->check($parishioner, $rule->service->slug);

            if ($result->isEligible()) {
                $user = \App\Models\User::where('parishioner_id', $parishioner->id)->first();
                if ($user) {
                    $user->notify(new \App\Notifications\EligibilityNotification(
                        $parishioner,
                        'eligible',
                        ['service' => $rule->service->name]
                    ));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Post-waiver eligibility check failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.eligibility-waivers.index')
            ->with('success', 'Waiver granted and logged.');
    }
}
