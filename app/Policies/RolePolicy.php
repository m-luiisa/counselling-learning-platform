<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function isAdmin(User $user) {
        return $user->role->title === 'admin';
    }
}
