<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(
        User $user,
        Company $company
    ): bool {
        return $user->id === $company->user_id;
    }

    public function update(
        User $user,
        Company $company
    ): bool {
        return $user->id === $company->user_id;
    }

    public function delete(
        User $user,
        Company $company
    ): bool {
        return $user->id === $company->user_id;
    }
}
