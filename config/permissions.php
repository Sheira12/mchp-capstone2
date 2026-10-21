<?php

/**
 * Ability → allowed roles mapping.
 *
 * ASSUMPTION (documented, easy to change):
 *   super_admin can VIEW requirements but cannot approve, reject, or request
 *   revisions. Only parish_secretary holds that ability.
 *   To grant super_admin approval rights, set
 *       'requirements' => ['review' => ['super_admin_override' => true]]
 *   below, then run: php artisan config:clear
 *
 * Finance keeps its current scope (payments / ledger / receipts) and has
 * NO ability to touch requirements at all.
 *
 * NOTE: Array keys use nested structure so Laravel's config() dot-notation
 * resolves correctly, e.g. config('permissions.requirements.review.allowed_roles').
 */
return [

    'requirements' => [

        /*
        |----------------------------------------------------------------------
        | Ability: requirements.review
        |----------------------------------------------------------------------
        | Approve, reject, or request revision on parishioner-submitted booking
        | requirement documents.
        | Enforced by: App\Policies\RequirementsPolicy::review()
        |              Gate::define('requirements.review') in AppServiceProvider
        | Route middleware: role:parish_secretary  (mutation routes only)
        |----------------------------------------------------------------------
        */
        'review' => [
            'allowed_roles' => ['parish_secretary'],
            // Set to true to also allow super_admin to approve/reject requirements.
            // See ASSUMPTION note above.
            'super_admin_override' => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Ability: requirements.view
        |----------------------------------------------------------------------
        | Read-only view of the requirements queue and individual submissions.
        | All admin roles can view — no action buttons shown to non-reviewers.
        |----------------------------------------------------------------------
        */
        'view' => [
            'allowed_roles' => ['parish_secretary', 'super_admin', 'finance_officer'],
        ],

    ],

];
