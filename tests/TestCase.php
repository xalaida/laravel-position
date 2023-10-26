<?php

namespace Nevadskiy\Position\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/App/Migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        Model::unguard();
    }

    /**
     * @inheritdoc
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
