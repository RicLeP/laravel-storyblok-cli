<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\ReadsStory;
use Riclep\StoryblokCli\SavesStoryJson;

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
    protected $description = 'Export a story as JSON';

	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

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
     * Execute the console command.
     *
     * @return int
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
				$this->info('Component not exported.');
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
