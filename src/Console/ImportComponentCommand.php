<?php

namespace RicLep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\Traits\GetsComponents;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

class ImportComponentCommand extends Command
{
	use GetsComponents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:import-component {file?} {--as=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import components from JSON definitions';

	/**
	 * @var ManagementClient
	 */
	protected ManagementClient $managementClient;


	public function __construct()
	{
		parent::__construct();

		$this->managementClient = new ManagementClient(env('STORYBLOK_OAUTH_TOKEN'));
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 * @throws ApiException
	 * @throws JsonException
	 */
    public function handle(): int
    {
		//// TODO validate component JSON

	    if (!Storage::exists($this->argument('file'))) {
		    $this->error('Component file not found: ' . $this->argument('file'));
		    exit;
	    }

		$this->requestComponents();

		$this->importComponent($this->argument('file'));

        return Command::SUCCESS;
    }

	/**
	 * @throws JsonException|ApiException
	 */
	protected function importComponent($componentFile)
	{
		$file = json_decode(Storage::get($componentFile), true, 512, JSON_THROW_ON_ERROR);
		unset($file['created_at'], $file['updated_at']);

		if ($this->option('as')) {
			$file['name'] = $this->option('as');
			$file['real_name'] = $this->option('as');
		}

		if ($this->sbComponents->firstWhere('name', $file['name'])) {
			return $this->updateComponent($this->sbComponents->firstWhere('name', $file['name'])['id'], $file);
		} else {
			return $this->createComponent($file);
		}
	}

	/**
	 * @param $component
	 * @param $file
	 * @return void
	 */
	protected function updateComponent($componentId, $file)
	{
		if ($this->confirm('Component already exists. Do you want to overwrite it?')) {
			$oldComponent = $this->requestComponent($componentId);

			$response = $this->managementClient->put('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/' . $componentId,
				[
					'component' => $file
				])->getBody();

//			dd($oldComponent, $response['component']);
			dd($oldComponent, $response, array_diff_assoc($oldComponent, $response['component']));

			$this->info('Component updated: ' . $file['name']);
		} else {
			$this->info('Component ' . $file['name'] . ' not imported. Use --as={name} to create a new component');
			exit;
		}
	}

	/**
	 * @param $file
	 * @return mixed
	 */
	private function createComponent($file)
	{
		$this->managementClient->post('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/', [
			'component' => $file
		])->getBody();

		$this->info('Component created: ' . $file['name']);
	}
}
