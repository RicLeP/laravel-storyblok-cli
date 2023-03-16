<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Riclep\StoryblokCli\Endpoints\Stories;
use Storyblok\ManagementClient;

class ImportStoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:import-story {file} {slug} {--P|publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a story from JSON - it will be created in your space’s root';

	protected string $path = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

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
		// TODO - interactive console for selecting save folder? Too complex?
	    // TODO - allow using slug from local JSON file
	    // TODO - import nested slugs
	    // TODO - allow updating stories
	    // TODO - validate story JSON

	    if (!$this->argument('file')) {
		    $this->error('No component file specified');
		    exit;
	    }

	    if (!Storage::exists($this->path . $this->argument('file'))) {
		    $this->error('Story file not found: ' . $this->argument('file'));
		    exit;
	    }


	    try {
		    $remoteStory = Stories::make()->bySlug($this->argument('slug'), true)->getStory();
	    } catch (\Exception $e) {
		    $remoteStory = null;
	    }

	    if (!$remoteStory) {
		    $source = json_decode(Storage::get($this->path . $this->argument('file')), true);

		    $story = [
			    "story" =>  [
				    "name" => $source['name'] . ' (Imported)',
				    "slug" => $this->argument('slug'),
				    "content" => $source['content'],
			    ],
			    "publish" =>  $this->option('publish')
		    ];

		    $importedStory = Stories::make()->create($story);

		    $this->info('Imported into Storyblok: ' . $source['name'] . ' (Imported)');
	    } else {
			$this->error('Story already exists in your space.');
	    }
    }
}
