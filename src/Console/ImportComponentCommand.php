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

		$this->requestComponents();

		$response = $this->importComponent($this->argument('file'));

		$this->info('Component imported successfully: ' . $response['component']['name']);

        return Command::SUCCESS;
    }

	/**
	 * @throws JsonException|ApiException
	 */
	protected function importComponent($componentFile)
	{
		if (!Storage::exists($componentFile)) {
			$this->error('Component file not found: ' . $componentFile);
			exit;
		}

		$file = json_decode(Storage::get($componentFile), true, 512, JSON_THROW_ON_ERROR);

		if ($this->option('as')) {
			$file['name'] = $this->option('as');
			$file['real_name'] = $this->option('as');
		}

		if ($component = $this->sbComponents->firstWhere('name', $file['name'])) {
			if ($this->confirm('Component already exists. Do you want to overwrite it?')) {
				unset($component['created_at'], $component['updated_at']);

				return $this->managementClient->put('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/' . $component['id'], [
					'component' => $file
				])->getBody();
			} else {
				$this->info('Component not imported. Use --as={name} to create a new component');
				exit;
			}
		}

		return $this->managementClient->post('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/', [
			'component' => $file
		])->getBody();
	}
}
