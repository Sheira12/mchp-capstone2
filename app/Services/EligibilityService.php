<?php

namespace App\Services;

use App\Models\BookingRequirement;
use App\Models\Certificate;
use App\Models\EligibilityRule;
use App\Models\EligibilityWaiver;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Models\SeminarRegistration;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * EligibilityService
 *
 * Given a Parishioner and a service slug, evaluates every active eligibility
 * rule and returns an EligibilityResult describing what is satisfied, what is
 * pending, and what is missing.
 *
 * Usage:
 *   $result = app(EligibilityService::class)->check($parishioner, 'wedding');
 *   if (!$result->isEligible()) { ... show $result->summary() ... }
 */
class EligibilityService
{
    /**
     * Evaluate all active eligibility rules for a given parishioner and service.
     *
     * @param  Parishioner  $parishioner  The primary booking applicant
     * @param  string       $serviceSlug  e.g. 'baptism', 'wedding'
     * @param  int|null     $bookingId    Optional — used to check per-booking waivers
     * @return EligibilityResult
     */
    public function check(Parishioner $parishioner, string $serviceSlug, ?int $bookingId = null): EligibilityResult
    {
        $service = Service::where('slug', $serviceSlug)->first();

        if (!$service) {
            // Unknown service — no rules — allow through
            return new EligibilityResult(true, collect(), $serviceSlug);
        }

        $rules = $service->eligibilityRules()->get();

        if ($rules->isEmpty()) {
            // No rules configured — open service
            return new EligibilityResult(true, collect(), $serviceSlug);
        }

        $items = $rules->map(function (EligibilityRule $rule) use ($parishioner, $bookingId) {
            return $this->evaluateRule($rule, $parishioner, $bookingId);
        });

        // Overall eligibility: every required rule must be satisfied (or waived)
        $eligible = $items->every(fn(EligibilityRuleResult $r) => !$r->required || $r->satisfied);

        return new EligibilityResult($eligible, $items, $serviceSlug);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Rule evaluators
    // ─────────────────────────────────────────────────────────────────────────

    private function evaluateRule(EligibilityRule $rule, Parishioner $parishioner, ?int $bookingId): EligibilityRuleResult
    {
        // Check waiver first — a waived rule is always satisfied
        if ($rule->isWaived($parishioner->id, $bookingId)) {
            $waiver = EligibilityWaiver::where('eligibility_rule_id', $rule->id)
                ->where('parishioner_id', $parishioner->id)
                ->when($bookingId, fn($q) => $q->where('booking_id', $bookingId))
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->first();

            return new EligibilityRuleResult(
                rule:          $rule,
                satisfied:     true,
                status:        'waived',
                message:       'Waived by parish office: ' . ($waiver?->reason ?? ''),
                actionUrl:     null,
                actionLabel:   null,
            );
        }

        return match ($rule->criterion_type) {
            EligibilityRule::TYPE_SEMINAR_COMPLETED      => $this->checkSeminarCompleted($rule, $parishioner),
            EligibilityRule::TYPE_DOCUMENT_APPROVED      => $this->checkDocumentApproved($rule, $parishioner),
            EligibilityRule::TYPE_PREREQUISITE_SACRAMENT => $this->checkPrerequisiteSacrament($rule, $parishioner),
            EligibilityRule::TYPE_MINIMUM_AGE            => $this->checkMinimumAge($rule, $parishioner),
            EligibilityRule::TYPE_PARISHIONER_STATUS     => $this->checkParishionerStatus($rule, $parishioner),
            EligibilityRule::TYPE_OTHER                  => $this->checkOther($rule, $parishioner),
            default                                      => $this->checkOther($rule, $parishioner),
        };
    }

    /** Check if parishioner has an 'attended' seminar registration for the correct service type. */
    private function checkSeminarCompleted(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        $serviceSlug  = $rule->param('service_slug');
        $validityDays = $rule->param('validity_days'); // null = no expiry

        $seminarService = $serviceSlug ? Service::where('slug', $serviceSlug)->first() : null;

        $query = SeminarRegistration::where('parishioner_id', $parishioner->id)
            ->where('status', 'attended')
            ->whereHas('seminar', function ($q) use ($seminarService) {
                $q->where('status', 'completed');
                if ($seminarService) {
                    $q->where('service_id', $seminarService->id);
                }
            });

        if ($validityDays) {
            $query->where('attended_at', '>=', now()->subDays($validityDays));
        }

        $reg = $query->latest('attended_at')->first();

        if ($reg) {
            $expiryNote = '';
            if ($validityDays && $reg->attended_at) {
                $expiresOn  = $reg->attended_at->addDays($validityDays);
                $expiryNote = ' (valid until ' . $expiresOn->format('M d, Y') . ')';
            }
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     'Seminar attended on ' . $reg->attended_at?->format('M d, Y') . $expiryNote,
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Not satisfied — check if they are registered (pending attendance)
        $pendingReg = SeminarRegistration::where('parishioner_id', $parishioner->id)
            ->whereIn('status', ['registered'])
            ->whereHas('seminar', function ($q) use ($seminarService) {
                $q->where('status', 'scheduled')->where('scheduled_at', '>', now());
                if ($seminarService) {
                    $q->where('service_id', $seminarService->id);
                }
            })
            ->first();

        if ($pendingReg) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   false,
                status:      'pending',
                message:     'Registered for seminar on ' . $pendingReg->seminar->scheduled_at->format('M d, Y') . '. Attendance must be confirmed.',
                actionUrl:   route('parishioner.seminars.index'),
                actionLabel: 'View Registration',
            );
        }

        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'missing',
            message:     'You have not yet completed the required seminar.',
            actionUrl:   route('parishioner.seminars.index'),
            actionLabel: 'Register for a Seminar',
        );
    }

    /** Check if a required document (via booking_requirements) is approved. */
    private function checkDocumentApproved(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        $docKey = $rule->param('document_key');

        // Look for an approved BookingRequirement for this parishioner
        // matching by the ServiceRequirement name/key or document_key param
        $approved = BookingRequirement::where('status', 'approved')
            ->whereHas('booking', fn($q) => $q->where('parishioner_id', $parishioner->id))
            ->whereHas('requirement', function ($q) use ($docKey, $rule) {
                if ($docKey) {
                    $q->where('name', 'like', '%' . $docKey . '%');
                } else {
                    $q->where('service_id', $rule->service_id);
                }
            })
            ->first();

        if ($approved) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     'Document approved.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Pending review
        $pending = BookingRequirement::where('status', 'pending')
            ->whereHas('booking', fn($q) => $q->where('parishioner_id', $parishioner->id))
            ->whereHas('requirement', function ($q) use ($docKey, $rule) {
                if ($docKey) {
                    $q->where('name', 'like', '%' . $docKey . '%');
                } else {
                    $q->where('service_id', $rule->service_id);
                }
            })
            ->first();

        if ($pending) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   false,
                status:      'pending',
                message:     'Document submitted — awaiting admin review.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'missing',
            message:     'Required document not yet submitted.',
            actionUrl:   route('parishioner.bookings.create'),
            actionLabel: 'Upload Document',
        );
    }

    /** Check that a prerequisite sacrament exists in sacramental_records OR as a released certificate. */
    private function checkPrerequisiteSacrament(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        $sacramentType = $rule->param('sacrament_type'); // e.g. 'baptism'

        if (!$sacramentType) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   false,
                status:      'missing',
                message:     'Prerequisite sacrament not configured (contact admin).',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Check sacramental_records first
        $record = SacramentalRecord::where('parishioner_id', $parishioner->id)
            ->where('type', $sacramentType)
            ->first();

        if ($record) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     ucfirst($sacramentType) . ' record found (administered ' . $record->date_administered?->format('M d, Y') . ').',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Check released certificates (Certificate::REQUIRES_RECORD types)
        $certType  = Certificate::TYPE_TO_SACRAMENT[$sacramentType] ?? $sacramentType;
        $certificate = Certificate::where('parishioner_id', $parishioner->id)
            ->where('type', $certType)
            ->where('status', 'released')
            ->first();

        if ($certificate) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     ucfirst($sacramentType) . ' certificate on record (cert #' . $certificate->certificate_number . ').',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'missing',
            message:     'No ' . ucfirst($sacramentType) . ' record found in the system. Please contact the parish office to have your record added.',
            actionUrl:   route('parishioner.certificates.create'),
            actionLabel: 'Request Certificate',
        );
    }

    /** Check parishioner age against minimum_age param. */
    private function checkMinimumAge(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        $minAge = (int) $rule->param('min_age', 0);

        if (!$parishioner->birthdate) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   false,
                status:      'missing',
                message:     'Birthdate not on your profile. Please update your profile.',
                actionUrl:   route('parishioner.profile'),
                actionLabel: 'Update Profile',
            );
        }

        $age = $parishioner->birthdate->age;

        if ($age >= $minAge) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     "Age requirement met (age {$age}, minimum {$minAge}).",
                actionUrl:   null,
                actionLabel: null,
            );
        }

        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'missing',
            message:     "Minimum age is {$minAge}. You are currently {$age} years old.",
            actionUrl:   null,
            actionLabel: null,
        );
    }

    /** Check that the parishioner profile is active. */
    private function checkParishionerStatus(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        if ($parishioner->is_active) {
            return new EligibilityRuleResult(
                rule:        $rule,
                satisfied:   true,
                status:      'satisfied',
                message:     'Parishioner profile is active.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'missing',
            message:     'Your parishioner profile is not active. Please contact the parish office.',
            actionUrl:   null,
            actionLabel: null,
        );
    }

    /** "Other" criterion — satisfied only when admin manually sets it via a waiver or approval. */
    private function checkOther(EligibilityRule $rule, Parishioner $parishioner): EligibilityRuleResult
    {
        // 'other' rules are satisfied by a waiver (handled before this method is reached)
        // or by an admin marking it in the eligibility waiver system.
        // Without a waiver, they are always pending (admin must review).
        return new EligibilityRuleResult(
            rule:        $rule,
            satisfied:   false,
            status:      'pending',
            message:     $rule->description ?? 'This requirement must be verified by the parish office.',
            actionUrl:   null,
            actionLabel: null,
        );
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  Value Objects
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Per-rule result — immutable value object.
 *
 * status: 'satisfied' | 'pending' | 'missing' | 'waived'
 */
class EligibilityRuleResult
{
    public readonly bool   $required;
    public readonly string $ruleName;
    public readonly string $criterionType;
    public readonly string $appliesTo;
    public readonly int    $ruleId;
    public readonly bool   $isPlaceholder;

    public function __construct(
        public readonly EligibilityRule $rule,
        public readonly bool            $satisfied,
        public readonly string          $status,   // satisfied|pending|missing|waived
        public readonly string          $message,
        public readonly ?string         $actionUrl,
        public readonly ?string         $actionLabel,
    ) {
        $this->required       = $rule->is_required;
        $this->ruleName       = $rule->name;
        $this->criterionType  = $rule->criterion_type;
        $this->appliesTo      = $rule->applies_to;
        $this->ruleId         = $rule->id;
        $this->isPlaceholder  = $rule->is_placeholder;
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'satisfied' => 'green',
            'pending'   => 'yellow',
            'waived'    => 'blue',
            default     => 'red',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'satisfied' => '✓ Satisfied',
            'pending'   => '⏳ Pending Review',
            'waived'    => '↷ Waived',
            default     => '✗ Not Met',
        };
    }
}

/**
 * Overall eligibility result for a parishioner + service.
 */
class EligibilityResult
{
    public function __construct(
        private readonly bool       $eligible,
        private readonly Collection $items,    // Collection<EligibilityRuleResult>
        private readonly string     $serviceSlug,
    ) {}

    public function isEligible(): bool
    {
        return $this->eligible;
    }

    public function items(): Collection
    {
        return $this->items;
    }

    /** Required rules that are NOT satisfied and not waived. */
    public function blocking(): Collection
    {
        return $this->items->filter(fn($r) => $r->required && !$r->satisfied);
    }

    /** Items currently pending admin review. */
    public function pending(): Collection
    {
        return $this->items->filter(fn($r) => $r->status === 'pending');
    }

    /** Items that are fully satisfied or waived. */
    public function satisfied(): Collection
    {
        return $this->items->filter(fn($r) => $r->satisfied);
    }

    /** Progress fraction for the progress bar. */
    public function progress(): array
    {
        $required = $this->items->filter(fn($r) => $r->required)->count();
        $done     = $this->items->filter(fn($r) => $r->required && $r->satisfied)->count();
        return [
            'satisfied' => $done,
            'total'     => $required,
            'pct'       => $required > 0 ? round(($done / $required) * 100) : 100,
        ];
    }

    /**
     * Simple array representation for JSON / Blade.
     */
    public function toArray(): array
    {
        return [
            'eligible'     => $this->eligible,
            'service_slug' => $this->serviceSlug,
            'progress'     => $this->progress(),
            'items'        => $this->items->map(fn($r) => [
                'rule_id'       => $r->ruleId,
                'name'          => $r->ruleName,
                'criterion'     => $r->criterionType,
                'applies_to'    => $r->appliesTo,
                'required'      => $r->required,
                'satisfied'     => $r->satisfied,
                'status'        => $r->status,
                'message'       => $r->message,
                'action_url'    => $r->actionUrl,
                'action_label'  => $r->actionLabel,
                'is_placeholder'=> $r->isPlaceholder,
            ])->values()->all(),
        ];
    }
}
