<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteServiceTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_exposes_ultrasound_and_monday_pediatric_services(): void
    {
        $response = $this->get(route('welcome'));

        $response->assertOk()
            ->assertSee('Ultrasound Services')
            ->assertSee('Pediatric Clinic')
            ->assertSee('every Monday');
    }
}
