<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_has_key_value(): void
    {
        Setting::create([
            'key' => 'clinic_name',
            'value' => 'Nyalife Women\'s Clinic',
        ]);

        $setting = Setting::where('key', 'clinic_name')->first();
        $this->assertSame('Nyalife Women\'s Clinic', $setting->value);
    }

    public function test_setting_clinic_contact_settings(): void
    {
        Setting::create(['key' => 'contact_address', 'value' => 'Nairobi, Kenya']);
        Setting::create(['key' => 'contact_phone', 'value' => '+254700000000']);
        Setting::create(['key' => 'contact_email', 'value' => 'info@nyalife.com']);

        $settings = Setting::clinicContactSettings();
        $this->assertInstanceOf(Collection::class, $settings);
        $this->assertSame('Nairobi, Kenya', $settings->get('contact_address'));
    }

    public function test_setting_clinic_invoice_settings(): void
    {
        Setting::create(['key' => 'tax_rate', 'value' => '16']);
        Setting::create(['key' => 'currency', 'value' => 'KES']);

        $settings = Setting::clinicInvoiceSettings();
        $this->assertInstanceOf(Collection::class, $settings);
    }

    public function test_setting_unique_key(): void
    {
        Setting::create(['key' => 'unique_test_key', 'value' => 'first']);

        $this->expectException(QueryException::class);
        Setting::create(['key' => 'unique_test_key', 'value' => 'second']);
    }
}
