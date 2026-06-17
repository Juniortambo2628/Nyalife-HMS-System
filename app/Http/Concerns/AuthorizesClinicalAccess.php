<?php

namespace App\Http\Concerns;

use App\Models\Patient;
use App\Support\Permissions;
use Illuminate\Support\Facades\Auth;

trait AuthorizesClinicalAccess
{
    protected function ownPatientId(): ?int
    {
        $user = Auth::user();

        if (! $user || ! $user->can(Permissions::VIEW_OWN_RECORDS)) {
            return null;
        }

        return Patient::where('user_id', $user->user_id)->value('patient_id');
    }

    protected function isPatientPortalUser(): bool
    {
        return $this->ownPatientId() !== null
            && ! Auth::user()?->hasAnyPermission([
                Permissions::MANAGE_PATIENTS,
                Permissions::MANAGE_APPOINTMENTS,
                Permissions::MANAGE_CONSULTATIONS,
            ]);
    }

    /**
     * Allow staff with any listed permission, or patients accessing their own record.
     */
    protected function requireStaffOrOwnPatient(?int $patientId, string ...$staffPermissions): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($staffPermissions !== [] && $user->hasAnyPermission($staffPermissions)) {
            return;
        }

        $ownPatientId = $this->ownPatientId();
        abort_unless($ownPatientId && $patientId && $ownPatientId === $patientId, 403);
    }
}
