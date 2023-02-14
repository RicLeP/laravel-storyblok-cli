<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Riclep\StoryblokCli\CreatesStories;
use Riclep\StoryblokCli\ReadsStory;
use Storyblok\ManagementClient;

class ImportStoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:import-story {filename} {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a story from JSON - it will be created in your space’s root';

	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->client = new ManagementClient(config('storyblok-cli.oauth_token'));

		parent::__construct();
	}

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ReadsStory $readsStory, CreatesStories $createsStories)
    {
		// TODO - interactive console for selecting save folder?


		if (!$readsStory->exists($this->argument('slug'))) {
			$source = json_decode(Storage::get($this->storagePath . $this->argument('filename')), true);

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
