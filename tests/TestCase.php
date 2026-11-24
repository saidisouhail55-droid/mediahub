<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment() !== 'testing') {
            throw new RuntimeException('Les tests doivent être exécutés avec APP_ENV=testing.');
        }

        if (config('database.default') !== 'mysql') {
            throw new RuntimeException('Les tests doivent utiliser la connexion MySQL.');
        }

        if (config('database.connections.mysql.database') !== 'mediahub_test') {
            throw new RuntimeException('Les tests sont interdits hors de la base mediahub_test.');
        }
    }
}
