<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true; // staff + portal customers (scoped in controllers)
    }

    public function update(User $user, Appointment $appointment): bool
    {
        // Staff advance any; portal customers may only cancel their own *booked* appointments.
        if ($user->customer_id) {
            return $user->customer_id === $appointment->customer_id
                && $appointment->status === 'booked';
        }

        return true;
    }
}