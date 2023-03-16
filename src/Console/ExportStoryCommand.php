<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Riclep\StoryblokCli\Endpoints\Stories;
use Riclep\StoryblokCli\Exporters\StoryExporter;

class ExportStoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:export-story {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save a story as JSON';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	    if (!$this->argument('slug')) {
		    $this->error('No slug specified.');
	    }

	    if (is_numeric($this->argument('slug'))) {
		    try {
		       $storyData = Stories::make()->byId($this->argument('slug'))->getStory();
		    } catch (\Exception $e) {
			    $this->error($e->getMessage());

			    exit;
		    }
	    } else {
		    try {
			    $storyData = Stories::make()->bySlug($this->argument('slug'), true)->getStory();
		    } catch (\Exception $e) {
			    $this->error($e->getMessage());

			    exit;
		    }
	    }

	    $storyExporter = new StoryExporter($storyData->toArray());

	    if ($storyExporter->exists()) {
		    if (!$this->confirm($storyExporter->getFilename() . ' already exists. Do you want to overwrite it?')) {
			    $this->info('Story not exported.');
			    exit;
		    }
	    }

	    if ($storyExporter->save()) {
		    $this->info('Story exported to storage: ' . $storyExporter->getPath() . $storyExporter->getFilename());
	    } else {
		    $this->error('Story not exported.');
	    }
    }
}
