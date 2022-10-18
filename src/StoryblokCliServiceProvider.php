<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\ServiceProvider;
use Riclep\StoryblokCli\Console\DiffComponentCommand;
use Riclep\StoryblokCli\Console\ExportComponentCommand;
use Riclep\StoryblokCli\Console\ImportComponentCommand;

class StoryblokCliServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
		$this->commands([
			DiffComponentCommand::class,
			ExportComponentCommand::class,
			ImportComponentCommand::class
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
