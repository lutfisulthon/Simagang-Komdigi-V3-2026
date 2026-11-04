<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaman: RefreshDatabase menghapus semua tabel, jadi tolak database yang bukan untuk test.
        $database = DB::connection()->getDatabaseName();

        if (! str_ends_with((string) $database, '_test')) {
            $this->fail("Test diarahkan ke database '{$database}'. Gunakan database berakhiran _test (lihat phpunit.xml).");
        }
    }
}
