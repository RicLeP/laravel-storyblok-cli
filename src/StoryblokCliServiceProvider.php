<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\ServiceProvider;
use Riclep\StoryblokCli\Console\ComponentListCommand;
use Riclep\StoryblokCli\Console\DiffComponentCommand;
use Riclep\StoryblokCli\Console\ExportComponentCommand;
use Riclep\StoryblokCli\Console\ExportStoryCommand;
use Riclep\StoryblokCli\Console\ImportComponentCommand;
use Riclep\StoryblokCli\Console\ImportStoryCommand;
use Riclep\StoryblokCli\Console\SpaceListCommand;

class StoryblokCliServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
		$this->commands([
			ComponentListCommand::class,
			DiffComponentCommand::class,
			ExportComponentCommand::class,
			ExportStoryCommand::class,
			ImportComponentCommand::class,
			ImportStoryCommand::class,
            SpaceListCommand::class
		]);
    }

	/**
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/storyblok-cli.php', 'storyblok-cli');
	}
}
