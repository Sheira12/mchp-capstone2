<?php

namespace App\Policies;

use App\Models\User;

/**
 * RequirementsPolicy
 *
 * Controls who can approve, reject, or request revisions on
 * parishioner-submitted booking requirement documents.
 *
 * The single `review` ability covers all three mutation actions
 * (approve / request-revision / approve-all).
 *
 * The authoritative config is config/permissions.php → requirements.review.
 * The AppServiceProvider also registers a named Gate ('requirements.review')
 * that reads the same config, so @can('requirements.review') in Blade and
 * Gate::authorize('requirements.review') in controllers both work without
 * needing a model instance.
 *
 * NOTE: The Gate::before super_admin bypass in AppServiceProvider explicitly
 * falls through for 'requirements.review' when super_admin_override = false.
 */
class RequirementsPolicy
{
    /**
     * Ability: requirements.review
     *
     * Approve, reject, or request revision on a booking requirement.
     * Called when a BookingRequirement model instance is passed to Gate::allows().
     */
    public function review(User $user): bool
    {
        $allowedRoles = config('permissions.requirements.review.allowed_roles', ['parish_secretary']);
        $saOverride   = config('permissions.requirements.review.super_admin_override', false);

        if ($saOverride && $user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole($allowedRoles);
    }

    /**
     * Ability: requirements.view
     *
     * Read-only access to the requirements queue.
     */
    public function view(User $user): bool
    {
        $allowedRoles = config(
            'permissions.requirements.view.allowed_roles',
            ['parish_secretary', 'super_admin', 'finance_officer']
        );

        return $user->hasRole($allowedRoles);
    }
}
