<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\ServiceProvider;
use Riclep\StoryblokCli\Console\ListComponentsCommand;
use Riclep\StoryblokCli\Console\DiffComponentCommand;
use Riclep\StoryblokCli\Console\ExportComponentCommand;
use Riclep\StoryblokCli\Console\ExportStoryCommand;
use Riclep\StoryblokCli\Console\ImportComponentCommand;
use Riclep\StoryblokCli\Console\ImportStoryCommand;

class StoryblokCliServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot()
    {
		$this->commands([
			ListComponentsCommand::class,
			DiffComponentCommand::class,
			ExportComponentCommand::class,
			ExportStoryCommand::class,
			ImportComponentCommand::class,
			ImportStoryCommand::class,
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
