<?php

namespace  RobertGDev\LaravelToon\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use RobertGDev\LaravelToon\ToonServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ToonServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Toon' => \RobertGDev\LaravelToon\Facades\Toon::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Setup default config for testing
        $app['config']->set('toon.encode.indent', 2);
        $app['config']->set('toon.encode.delimiter', ',');
        $app['config']->set('toon.encode.lengthMarker', false);
        $app['config']->set('toon.decode.indent', 2);
        $app['config']->set('toon.decode.strict', true);
        $app['config']->set('toon.decode.objectsAsStdClass', false);
    }
}