<?php

namespace Riclep\StoryblokCli\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Riclep\StoryblokCli\StoryblokCliServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            StoryblokCliServiceProvider::class,
        ];
    }
}
