<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     * RefreshDatabase automatically runs migrations before each test,
     * so we do NOT call artisan('migrate') manually here.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
