<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    protected $signature = 'ls:import-component {file?} {--as=} {--group=false}';

	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

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

		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
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

	    if (!Storage::exists($this->storagePath . $this->argument('file'))) {
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
		$importSchema = json_decode(Storage::get($this->storagePath . $componentFile), true, 512, JSON_THROW_ON_ERROR);
		unset($importSchema['created_at'], $importSchema['updated_at']);

		if ($this->option('as')) {
			$importSchema['name'] = $this->option('as');
			$importSchema['real_name'] = $this->option('as');
		}

		// don’t like this but not sure how to have a default value which prompts for a choice and
		// is skipped when now passed as an option
		if ($this->option('group') !== 'false') {
			$importSchema = $this->setComponentGroup($importSchema);
		}

		if ($this->sbComponents->firstWhere('name', $importSchema['name'])) {
			return $this->updateComponent(
				$this->sbComponents
					->firstWhere('name', $importSchema['name'])['id'], $importSchema
			);
		} else {
			return $this->createComponent($importSchema);
		}
	}

	protected function setComponentGroup($importSchema)
	{
		if ($this->option('group') === null) {
			$componentGroupName = $this->choice(
				'Select components to export',
				$this->sbComponentGroups->pluck('name')->toArray()
			);

			$group = $this->sbComponentGroups->filter(fn($group) => $group['name'] === $componentGroupName)->first();
		} else if (Str::isUuid($this->option('group'))) {
			$group = $this->sbComponentGroups->filter(fn($group) => $group['uuid'] === $this->option('group'))->first();
		} else {
			$group = $this->sbComponentGroups->filter(fn($group) => $group['id'] === (int) $this->option('group'))->first();
		}

		if (!$group) {
			$this->error('Component group not found');
			exit;
		}

		$importSchema['component_group_uuid'] = $group['uuid'];

		return $importSchema;
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

		$this->call('ls:diff-component', [
			'file' => $this->argument('file'),
		]);

		if ($this->confirm('Do you want to update the live schema?')) {
			// TODO - add option to backup existing schema

			$this->managementClient->put('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId,
				[
					'component' => $importSchema
				])->getBody();

			$this->info('Component updated: ' . $importSchema['name']);
		} else {
			$this->info('Component ' . $importSchema['name'] . ' not imported.');
			exit;
		}
	}

	/**
	 * @param $importSchema
	 * @return mixed
	 */
	protected function createComponent($importSchema)
	{
		$this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/components/', [
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
