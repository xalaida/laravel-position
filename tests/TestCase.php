<?php

namespace Nevadskiy\Position\Tests;

use Illuminate\Foundation\Application;
use Mockery;
use Mockery\Mock;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Support/Migrations');

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
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

    /**
     * Fake the model instance.
     *
     * @experimental
     * @return Mock|mixed
     */
    protected function fakeModel(string $className)
    {
        $model = Mockery::mock($className)->makePartial();
        $model->shouldReceive('newInstance')->andReturnSelf();
        $model->__construct();

        return $model;
    }
}
