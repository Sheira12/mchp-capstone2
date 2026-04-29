<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        // Admin roles can view all bookings
        if ($user->hasRole(['super_admin', 'parish_secretary', 'finance_officer'])) {
            return true;
        }

        // Parishioners can only view their own bookings
        return $user->parishioner && $booking->parishioner_id === $user->parishioner->id;
    }
}
