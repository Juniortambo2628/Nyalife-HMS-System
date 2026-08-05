<?php

namespace App\Http\Controllers;

use App\Http\Concerns\AuthorizesClinicalAccess;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use AuthorizesClinicalAccess;

    protected function requirePermission(string ...$permissions): void
    {
        $user = Auth::user();

        abort_unless(
            $user && ($permissions === [] || $user->hasAnyPermission($permissions)),
            403,
            'You do not have permission to perform this action.'
        );
    }
}
