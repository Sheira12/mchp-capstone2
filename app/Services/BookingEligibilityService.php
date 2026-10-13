<?php

namespace App\Services;

use App\Models\ParishionerRequirement;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Models\ServiceRequirement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * BookingEligibilityService
 *
 * Single source of truth for "can this parishioner book this service?".
 *
 * Uses service_requirements (with eligibility_type) + parishioner_requirements
 * (self-declared, staff-recorded, system-matched) to evaluate eligibility.
 *
 * This is the v2 gate; it works in parallel with the existing EligibilityRule/
 * EligibilityService system. BookingController@store calls this one first.
 *
 * Usage:
 *   $result = app(BookingEligibilityService::class)->check($parishioner, 'baptism');
 *   if (!$result->eligible) { ... show $result->items ... }
 */
class BookingEligibilityService
{
    /**
     * Evaluate all active gate-level service_requirements for a service slug.
     *
     * @param  Parishioner  $parishioner
     * @param  string       $serviceSlug  e.g. 'baptism', 'wedding'
     * @return BookingEligibilityResult
     */
    public function check(Parishioner $parishioner, string $serviceSlug): BookingEligibilityResult
    {
        // Fetch active gate-level requirements for this service slug
        $requirements = ServiceRequirement::where(function ($q) use ($serviceSlug) {
                $q->where('service_slug', $serviceSlug)
                  ->orWhereHas('service', fn($sq) => $sq->where('slug', $serviceSlug));
            })
            ->where('is_active', true)
            ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
            ->orderBy('sort_order')
            ->get();

        // No gate requirements → open service
        if ($requirements->isEmpty()) {
            return new BookingEligibilityResult(true, collect(), $serviceSlug);
        }

        $items = $requirements->map(fn($req) => $this->evaluateRequirement($req, $parishioner));

        // Allow booking with pending if setting is enabled
        $allowPending = \App\Models\Setting::get('allow_booking_with_pending_requirements', 'false') === 'true';

        if ($allowPending) {
            // Eligible unless something is explicitly rejected or missing
            $eligible = $items->every(fn($i) => in_array($i->state, ['satisfied', 'pending']));
        } else {
            // Default: every required item must be satisfied
            $eligible = $items->every(fn($i) => !$i->isRequired || $i->state === 'satisfied');
        }

        return new BookingEligibilityResult($eligible, $items, $serviceSlug);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function evaluateRequirement(ServiceRequirement $req, Parishioner $parishioner): BookingEligibilityItem
    {
        $type = $req->getEffectiveEligibilityType();

        return match ($type) {
            'sacrament_record' => $this->checkSacramentRecord($req, $parishioner),
            'seminar'          => $this->checkSeminarOrDeclaration($req, $parishioner),
            'document'         => $this->checkDocumentDeclaration($req, $parishioner),
            default            => $this->checkDocumentDeclaration($req, $parishioner),
        };
    }

    /**
     * sacrament_record: satisfied if a matching SacramentalRecord exists.
     * Auto-creates a system_matched ParishionerRequirement row for audit trail.
     */
    private function checkSacramentRecord(ServiceRequirement $req, Parishioner $parishioner): BookingEligibilityItem
    {
        $sacramentType = $req->required_sacrament_type;

        if (!$sacramentType) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'missing',
                message:     'Requirement misconfigured (no sacrament type set). Contact the parish office.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        $record = SacramentalRecord::where('parishioner_id', $parishioner->id)
            ->where('type', $sacramentType)
            ->first();

        if ($record) {
            // Auto-create or update a system_matched row so the UI can show it
            $this->upsertSystemMatched($parishioner->id, $req->id);

            return new BookingEligibilityItem(
                requirement: $req,
                state:       'satisfied',
                message:     ucfirst($sacramentType) . ' record found (administered ' . $record->date_administered?->format('M d, Y') . ').',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Also accept a verified parishioner_requirement for this req (in case
        // they self-declared with a certificate from another parish)
        $declared = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $req->id)
            ->where('status', 'verified')
            ->first();

        if ($declared) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'satisfied',
                message:     ucfirst($sacramentType) . ' verified by parish office.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Check if pending
        $pending = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $req->id)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'pending',
                message:     'Declaration submitted — awaiting parish office verification.',
                actionUrl:   route('parishioner.my-requirements.index'),
                actionLabel: 'View Status',
            );
        }

        // Check if rejected
        $rejected = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $req->id)
            ->where('status', 'rejected')
            ->first();

        if ($rejected) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'rejected',
                message:     'Previous declaration was rejected: ' . ($rejected->rejection_reason ?? '—') . '. Please resubmit.',
                actionUrl:   route('parishioner.my-requirements.declare', $req),
                actionLabel: 'Resubmit',
            );
        }

        return new BookingEligibilityItem(
            requirement: $req,
            state:       'missing',
            message:     'No ' . ucfirst($sacramentType) . ' record found. If you were baptized/confirmed elsewhere, you can declare it.',
            actionUrl:   route('parishioner.my-requirements.declare', $req),
            actionLabel: 'Declare ' . ucfirst($sacramentType),
        );
    }

    /**
     * seminar: satisfied if a verified ParishionerRequirement or
     * an attended SeminarRegistration exists.
     */
    private function checkSeminarOrDeclaration(ServiceRequirement $req, Parishioner $parishioner): BookingEligibilityItem
    {
        // Check direct seminar attendance in the system
        $seminarAttended = \App\Models\SeminarRegistration::where('parishioner_id', $parishioner->id)
            ->where('status', 'attended')
            ->whereHas('seminar', fn($q) => $q->whereHas('service', fn($sq) => $sq->where('slug', $req->service_slug ?? '')))
            ->first();

        if ($seminarAttended) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'satisfied',
                message:     'Seminar attended on ' . $seminarAttended->attended_at?->format('M d, Y') . '.',
                actionUrl:   null,
                actionLabel: null,
            );
        }

        // Check self-declared / staff-recorded
        return $this->checkDeclaration($req, $parishioner,
            'Seminar not yet recorded.',
            'Have you attended this seminar elsewhere? Declare it.',
        );
    }

    /**
     * document: satisfied if a verified ParishionerRequirement exists.
     */
    private function checkDocumentDeclaration(ServiceRequirement $req, Parishioner $parishioner): BookingEligibilityItem
    {
        return $this->checkDeclaration($req, $parishioner,
            'Document not yet submitted.',
            'Submit this document.',
        );
    }

    /** Shared declaration check (seminar + document). */
    private function checkDeclaration(
        ServiceRequirement $req,
        Parishioner $parishioner,
        string $missingMsg,
        string $missingAction,
    ): BookingEligibilityItem {
        $pr = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('service_requirement_id', $req->id)
            ->latest()
            ->first();

        if (!$pr) {
            return new BookingEligibilityItem(
                requirement: $req,
                state:       'missing',
                message:     $missingMsg,
                actionUrl:   route('parishioner.my-requirements.declare', $req),
                actionLabel: $missingAction,
            );
        }

        return match ($pr->status) {
            'verified' => new BookingEligibilityItem(
                requirement: $req,
                state:       'satisfied',
                message:     'Verified by parish office' . ($pr->date_completed ? ' (completed ' . $pr->date_completed->format('M d, Y') . ')' : '') . '.',
                actionUrl:   null,
                actionLabel: null,
            ),
            'pending' => new BookingEligibilityItem(
                requirement: $req,
                state:       'pending',
                message:     'Submitted — awaiting parish office review.',
                actionUrl:   route('parishioner.my-requirements.index'),
                actionLabel: 'View Status',
            ),
            'rejected' => new BookingEligibilityItem(
                requirement: $req,
                state:       'rejected',
                message:     'Rejected: ' . ($pr->rejection_reason ?? '—') . '. Please resubmit.',
                actionUrl:   route('parishioner.my-requirements.declare', $req),
                actionLabel: 'Resubmit',
            ),
            default => new BookingEligibilityItem(
                requirement: $req,
                state:       'missing',
                message:     $missingMsg,
                actionUrl:   route('parishioner.my-requirements.declare', $req),
                actionLabel: $missingAction,
            ),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Auto-match helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Upsert a system_matched parishioner_requirement row for audit trail.
     * Non-fatal if it fails.
     */
    private function upsertSystemMatched(int $parishionerId, int $reqId): void
    {
        try {
            ParishionerRequirement::updateOrCreate(
                ['parishioner_id' => $parishionerId, 'service_requirement_id' => $reqId],
                [
                    'status'     => 'verified',
                    'source'     => 'system_matched',
                    'verified_at'=> now(),
                    'verified_by'=> null,
                ]
            );
        } catch (\Exception $e) {
            Log::warning('autoMatch upsert failed: ' . $e->getMessage());
        }
    }

    /**
     * Scan all sacramental_records for a parishioner and create system_matched
     * parishioner_requirement rows for any service_requirement of type
     * sacrament_record that match.
     *
     * Call this after a sacramental record is created/updated, or on login.
     */
    public function autoMatchSacraments(Parishioner $parishioner): void
    {
        try {
            $records = SacramentalRecord::where('parishioner_id', $parishioner->id)->get();

            foreach ($records as $record) {
                $reqs = ServiceRequirement::where('eligibility_type', 'sacrament_record')
                    ->where('required_sacrament_type', $record->type)
                    ->where('is_active', true)
                    ->get();

                foreach ($reqs as $req) {
                    $this->upsertSystemMatched($parishioner->id, $req->id);
                }
            }
        } catch (\Exception $e) {
            Log::warning('autoMatchSacraments failed for parishioner ' . $parishioner->id . ': ' . $e->getMessage());
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  Value Objects
// ─────────────────────────────────────────────────────────────────────────────

class BookingEligibilityItem
{
    public readonly bool   $isRequired;
    public readonly string $requirementName;
    public readonly string $eligibilityType;
    public readonly int    $requirementId;

    public function __construct(
        public readonly ServiceRequirement $requirement,
        public readonly string             $state,         // satisfied|pending|rejected|missing
        public readonly string             $message,
        public readonly ?string            $actionUrl,
        public readonly ?string            $actionLabel,
    ) {
        $this->isRequired      = $requirement->is_required;
        $this->requirementName = $requirement->name;
        $this->eligibilityType = $requirement->getEffectiveEligibilityType();
        $this->requirementId   = $requirement->id;
    }

    public function isSatisfied(): bool { return $this->state === 'satisfied'; }
    public function isPending(): bool   { return $this->state === 'pending'; }
    public function isRejected(): bool  { return $this->state === 'rejected'; }
    public function isMissing(): bool   { return $this->state === 'missing'; }

    public function stateColor(): string
    {
        return match ($this->state) {
            'satisfied' => 'green',
            'pending'   => 'yellow',
            'rejected'  => 'red',
            default     => 'gray',
        };
    }

    public function stateLabel(): string
    {
        return match ($this->state) {
            'satisfied' => '✓ Satisfied',
            'pending'   => '⏳ Pending Review',
            'rejected'  => '✗ Rejected',
            default     => '○ Not Submitted',
        };
    }

    public function toArray(): array
    {
        return [
            'requirement_id'  => $this->requirementId,
            'name'            => $this->requirementName,
            'eligibility_type'=> $this->eligibilityType,
            'is_required'     => $this->isRequired,
            'state'           => $this->state,
            'message'         => $this->message,
            'action_url'      => $this->actionUrl,
            'action_label'    => $this->actionLabel,
        ];
    }
}

class BookingEligibilityResult
{
    public function __construct(
        public readonly bool       $eligible,
        public readonly Collection $items,
        public readonly string     $serviceSlug,
    ) {}

    public function isEligible(): bool { return $this->eligible; }

    public function blocking(): Collection
    {
        return $this->items->filter(fn($i) => $i->isRequired && !$i->isSatisfied());
    }

    public function pendingItems(): Collection
    {
        return $this->items->filter(fn($i) => $i->isPending());
    }

    public function hasPendingItems(): bool
    {
        return $this->pendingItems()->isNotEmpty();
    }

    public function progress(): array
    {
        $req  = $this->items->filter(fn($i) => $i->isRequired)->count();
        $done = $this->items->filter(fn($i) => $i->isRequired && $i->isSatisfied())->count();
        return [
            'satisfied' => $done,
            'total'     => $req,
            'pct'       => $req > 0 ? round(($done / $req) * 100) : 100,
        ];
    }

    public function toArray(): array
    {
        return [
            'eligible'     => $this->eligible,
            'service_slug' => $this->serviceSlug,
            'progress'     => $this->progress(),
            'items'        => $this->items->map(fn($i) => $i->toArray())->values()->all(),
        ];
    }
}
