<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Storyblok\ManagementClient;

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
    public function handle()
    {
	    $storyExists = $this->client->get('spaces/' . config('storyblok-cli.space_id') . '/stories/', [
			'with_slug' => $this->argument('slug')
	    ])->getBody()['stories'];

	    if ($storyExists) {
			$filename = Str::of($this->argument('slug'))->replace('/', '-')->slug() . '.json';

			if (Storage::exists($this->storagePath . $filename)) {
				if (!$this->confirm($filename . ' already exists. Do you want to overwrite it?')) {
					$this->info('Component not exported.');
					exit;
				}
			}

			$story = $this->client->get('spaces/' . config('storyblok-cli.space_id') . '/stories/' . $storyExists[0]['id'])->getBody();

			$json = json_encode($story, JSON_PRETTY_PRINT);

			Storage::put($this->storagePath . $filename, $json);

			$this->info('Saved to storage: ' . $filename);
		} else {
			$this->warn('There is no story for your slug: ' . $this->argument('slug'));
		}
    }
}
