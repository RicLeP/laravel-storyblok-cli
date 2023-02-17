<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Riclep\StoryblokCli\CreatesStories;
use Riclep\StoryblokCli\ReadsStories;

class ImportStoryCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ls:import-story {filename} {slug}';

    /**

     * @var string
     */
    protected $description = 'Import a story from JSON - it will be created in your space’s root';

	/**
	 * @var string
	 */
	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

	/**
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * Imports a story into Storyblok from a JSON file
     *
     * @return void
     */
    public function handle(ReadsStories $readsStory, CreatesStories $createsStories)
    {

		if (!$source = json_decode(Storage::get($this->storagePath . $this->argument('filename')), true)) {
			$this->error('Could not read JSON file: ' . $this->argument('filename'));

			exit;
		}

		// TODO - interactive console for selecting save folder?
		if (!$readsStory->exists($this->argument('slug'))) {
			$story = [
				"story" =>  [
					"name" => $source['story']['name'] . ' (Imported)',
					"slug" => $this->argument('slug'),
					"content" => $source['story']['content'],
				],
				"publish" =>  1
			];

			$importedStory = $createsStories->create($story);

			$this->info('Imported into Storyblok: ' . $importedStory['name']);
		} else {
			$this->warn('Story already exists for: ' . $this->argument('slug'));
		}
    }
}
