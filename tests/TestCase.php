<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Force testing database configuration BEFORE parent setUp runs migrations
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=C:\wamp64\www\Nyalife-HMS-System\database\testing.sqlite');
        
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
