<?php

namespace App\Support;

use App\Models\Patient;

class PatientId
{
    public static function format(int|string|null $id, string $prefix = 'PAT'): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        return $prefix.'-'.$id;
    }

    public static function selectLabel(?string $name, int|string|null $id, string $prefix = 'PAT'): string
    {
        $formattedId = self::format($id, $prefix);
        $trimmed = trim((string) $name);

        if ($formattedId === '') {
            return $trimmed;
        }

        if ($trimmed !== '' && str_contains($trimmed, $formattedId)) {
            return $trimmed;
        }

        if ($trimmed === '') {
            return $formattedId;
        }

        return "{$trimmed} ({$formattedId})";
    }

    public static function fromPatient(?Patient $patient): string
    {
        if (! $patient) {
            return '';
        }

        $name = trim(($patient->user->first_name ?? '').' '.($patient->user->last_name ?? ''));

        return self::selectLabel($name, $patient->patient_id);
    }
}
