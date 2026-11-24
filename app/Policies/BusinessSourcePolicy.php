<?php

namespace App\Policies;

use App\Models\BusinessSource;
use App\Models\User;

class BusinessSourcePolicy
{
    public function view(User $user, BusinessSource $source): bool
    {
        return $user->id === $source->business->user_id;
    }

    public function update(User $user, BusinessSource $source): bool
    {
        return $user->id === $source->business->user_id;
    }

    public function delete(User $user, BusinessSource $source): bool
    {
        return $user->id === $source->business->user_id;
    }
}
