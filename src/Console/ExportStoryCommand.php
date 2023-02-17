<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Riclep\StoryblokCli\ReadsStory;
use Riclep\StoryblokCli\SavesStoryJson;

class ExportStoryCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ls:export-story {slug}';

    /**
     * @var string
     */
    protected $description = 'Export a story as JSON to the storage folder';

	/**
	 * @var string
	 */
	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

	/**
	 * @var ReadsStory
	 */
	protected $storyReader;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(ReadsStory $ReadsStory)
	{
		parent::__construct();

		$this->storyReader = $ReadsStory;
	}

    /**
     * Exports a story as JSON to the storage folder
     *
     * @return void
     */
    public function handle()
    {
	    if (!$this->argument('slug')) {
		    $this->error('No slug specified');
	    }

	    if (is_numeric($this->argument('slug'))) {
		    try {
			    $story = $this->storyReader->requestById($this->argument('slug'));
		    } catch (\Exception $e) {
			    $this->error($e->getMessage());

			    exit;
		    }
	    } else {
		    try {
			    $story = $this->storyReader->requestBySlug($this->argument('slug'));
		    } catch (\Exception $e) {
			    $this->error($e->getMessage());

			    exit;
		    }
	    }

		$savesStoryJson = new SavesStoryJson($story);

		if ($savesStoryJson->exportExists($this->storagePath)) {
			if (!$this->confirm($savesStoryJson->filename . ' already exists. Do you want to overwrite it?')) {
				$this->info('Story not exported.');
				exit;
			}
		}

	    if ($savesStoryJson->save($this->storagePath)) {
			$this->info('Story exported to storage: ' . $this->storagePath . $savesStoryJson->filename);
	    } else {
		    $this->error('Story not exported.');
	    }
    }
}
