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

	    if (!$this->argument('file')) {
		    $this->error('No component file specified');
		    exit;
	    }

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
		$importSchema = json_decode(Storage::get($componentFile), true, 512, JSON_THROW_ON_ERROR);
		unset($importSchema['created_at'], $importSchema['updated_at']);

		if ($this->option('as')) {
			$importSchema['name'] = $this->option('as');
			$importSchema['real_name'] = $this->option('as');
		}

		if ($this->sbComponents->firstWhere('name', $importSchema['name'])) {
			return $this->updateComponent($this->sbComponents->firstWhere('name', $importSchema['name'])['id'], $importSchema);
		} else {
			return $this->createComponent($importSchema);
		}
	}

	/**
	 * @param $component
	 * @param $importSchema
	 * @return void
	 */
	protected function updateComponent($componentId, $importSchema)
	{
		$this->warn('Component already exists: ' . $importSchema['name'] . '.');
		$this->line('Use --as={name} to import as a new component');

		if ($this->hasChanges($importSchema)) {
			if ($this->confirm('Do you want to update the live schema?', true)) {
				$response = $this->managementClient->put('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/' . $componentId,
					[
						'component' => $importSchema
					])->getBody();

				$this->info('Component updated: ' . $importSchema['name']);
			} else {
				$this->info('Component ' . $importSchema['name'] . ' not imported.');
				exit;
			}
		}
	}

	/**
	 * @param $importSchema
	 * @return mixed
	 */
	protected function createComponent($importSchema)
	{
		$this->managementClient->post('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/', [
			'component' => $importSchema
		])->getBody();

		$this->info('Component created: ' . $importSchema['name']);
	}

	/**
	 * @param $importSchema
	 * @return void
	 * @throws JsonException
	 */
	protected function hasChanges($importSchema)
	{
		$existingSchema = $this->requestComponent($this->sbComponents->firstWhere('name', $importSchema['name'])['id']);
		unset($existingSchema['created_at'], $existingSchema['updated_at']);

		$treeWalker = new \TreeWalker(['returntype' => 'array']);
		$changes = $treeWalker->getdiff(
			json_encode($importSchema, JSON_THROW_ON_ERROR),
			json_encode($existingSchema, JSON_THROW_ON_ERROR)
		);

		if (empty(array_filter($changes))) {
			$this->info('No changes found, import cancelled');

			return false;
		}

		$this->line('');
		$this->info('Changes found:');

		dump($changes);
		return true;
	}
}
