<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\ServiceProvider;
use Riclep\StoryblokCli\Console\DiffComponentCommand;
use Riclep\StoryblokCli\Console\ExportComponentCommand;
use Riclep\StoryblokCli\Console\ExportStoryCommand;
use Riclep\StoryblokCli\Console\ImportComponentCommand;
use Riclep\StoryblokCli\Console\ImportStoryCommand;
use Riclep\StoryblokCli\Console\ListComponentsCommand;
use Riclep\StoryblokCli\Console\ListSpacesCommand;

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
			ExportStoryCommand::class,
			ImportComponentCommand::class,
			ImportStoryCommand::class,
			ListComponentsCommand::class,
			ListSpacesCommand::class
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
