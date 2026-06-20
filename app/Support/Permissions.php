<?php

namespace App\Support;

final class Permissions
{
    // Phase 9 — new modules
    public const MANAGE_PAYMENTS = 'manage-payments';
    public const MANAGE_FOLLOW_UPS = 'manage-follow-ups';
    public const MANAGE_DEPARTMENTS = 'manage-departments';
    public const VIEW_DEPARTMENTS = 'view-departments';
    public const VIEW_REPORTS = 'view-reports';
    public const MANAGE_SETTINGS = 'manage-settings';

    // Hardening — core HMS modules
    public const MANAGE_PATIENTS = 'manage-patients';
    public const VIEW_OWN_RECORDS = 'view-own-records';
    public const MANAGE_APPOINTMENTS = 'manage-appointments';
    public const MANAGE_CONSULTATIONS = 'manage-consultations';
    public const MANAGE_PRESCRIPTIONS = 'manage-prescriptions';
    public const MANAGE_INVOICES = 'manage-invoices';
    public const MANAGE_LAB = 'manage-lab';
    public const MANAGE_LAB_CATALOG = 'manage-lab-catalog';
    public const MANAGE_PHARMACY = 'manage-pharmacy';
    public const MANAGE_VITALS = 'manage-vitals';
    public const MANAGE_USERS = 'manage-users';
    public const MANAGE_SYSTEM = 'manage-system';
    public const MANAGE_INSURANCE = 'manage-insurance';
    public const SEND_MESSAGES = 'send-messages';

    public static function all(): array
    {
        return [
            self::MANAGE_PAYMENTS,
            self::MANAGE_FOLLOW_UPS,
            self::MANAGE_DEPARTMENTS,
            self::VIEW_DEPARTMENTS,
            self::VIEW_REPORTS,
            self::MANAGE_SETTINGS,
            self::MANAGE_PATIENTS,
            self::VIEW_OWN_RECORDS,
            self::MANAGE_APPOINTMENTS,
            self::MANAGE_CONSULTATIONS,
            self::MANAGE_PRESCRIPTIONS,
            self::MANAGE_INVOICES,
            self::MANAGE_LAB,
            self::MANAGE_LAB_CATALOG,
            self::MANAGE_PHARMACY,
            self::MANAGE_VITALS,
            self::MANAGE_USERS,
            self::MANAGE_SYSTEM,
            self::MANAGE_INSURANCE,
            self::SEND_MESSAGES,
        ];
    }

    /** Middleware string: staff permission OR patient portal access. */
    public static function staffOrPatient(string $staffPermission): string
    {
        return $staffPermission . '|' . self::VIEW_OWN_RECORDS;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function roleMap(): array
    {
        return [
            'admin' => self::all(),
            'doctor' => [
                self::MANAGE_PATIENTS,
                self::MANAGE_APPOINTMENTS,
                self::MANAGE_CONSULTATIONS,
                self::MANAGE_PRESCRIPTIONS,
                self::MANAGE_LAB,
                self::MANAGE_FOLLOW_UPS,
                self::MANAGE_PAYMENTS,
                self::VIEW_DEPARTMENTS,
                self::VIEW_REPORTS,
                self::SEND_MESSAGES,
            ],
            'nurse' => [
                self::MANAGE_PATIENTS,
                self::MANAGE_APPOINTMENTS,
                self::MANAGE_CONSULTATIONS,
                self::MANAGE_PRESCRIPTIONS,
                self::MANAGE_LAB,
                self::MANAGE_VITALS,
                self::MANAGE_FOLLOW_UPS,
                self::VIEW_DEPARTMENTS,
                self::SEND_MESSAGES,
            ],
            'receptionist' => [
                self::MANAGE_PATIENTS,
                self::MANAGE_APPOINTMENTS,
                self::MANAGE_INVOICES,
                self::MANAGE_PAYMENTS,
                self::MANAGE_INSURANCE,
                self::VIEW_DEPARTMENTS,
                self::VIEW_REPORTS,
                self::SEND_MESSAGES,
            ],
            'lab_technician' => [
                self::MANAGE_LAB,
                self::VIEW_DEPARTMENTS,
                self::SEND_MESSAGES,
            ],
            'pharmacist' => [
                self::MANAGE_PRESCRIPTIONS,
                self::MANAGE_PHARMACY,
                self::VIEW_DEPARTMENTS,
                self::SEND_MESSAGES,
            ],
            'patient' => [
                self::VIEW_OWN_RECORDS,
            ],
        ];
    }
}
