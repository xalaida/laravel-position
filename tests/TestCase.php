<?php

namespace Nevadskiy\Position\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Support/Migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
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
