<?php

namespace App\Policies;

use App\Models\River;
use App\Models\User;

class RiverPolicy
{
    public function update(User $user, River $river): bool
    {
        return $user->id === $river->created_by;
    }

    public function delete(User $user, River $river): bool
    {
        return $user->id === $river->created_by;
    }
}
