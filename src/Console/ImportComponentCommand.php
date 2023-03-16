<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Riclep\StoryblokCli\Endpoints\ComponentGroups;
use Riclep\StoryblokCli\Endpoints\Components;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

class ImportComponentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:import-component {file} {--as=} {--group=false}';

	/**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import components from JSON definitions';

	protected $path = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;


	public function __construct()
	{
		parent::__construct();
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
	    if (!$this->argument('file')) {
		    $this->error('No component file specified');
		    exit;
	    }

	    if (!Storage::exists($this->path . $this->argument('file'))) {
		    $this->error('Component file not found: ' . $this->argument('file'));
		    exit;
	    }

	    $this->importComponent();

        return Command::SUCCESS;
    }

	/**
	 * @throws JsonException|ApiException
	 */
	protected function importComponent()
	{
		$this->argument('file');

		$importSchema = json_decode(Storage::get($this->path . $this->argument('file')), true, 512, JSON_THROW_ON_ERROR);
		unset($importSchema['created_at'], $importSchema['updated_at']);

		if ($this->option('as')) {
			$importSchema['name'] = $this->option('as');
			$importSchema['real_name'] = $this->option('as');
		}

		// don’t like this but not sure how to have a default value which prompts for a choice and
		// is skipped when now passed as an option
		if ($this->option('group') && $this->option('group') !== 'false') {
			$importSchema = $this->setComponentGroup($importSchema);
		} else {
			$importSchema = $this->selectComponentGroup($importSchema);
		}

		$components = Components::make()->all()->getComponents();

		if ($components->firstWhere('name', $importSchema['name'])) {
			return $this->updateComponent(
				$components->firstWhere('name', $importSchema['name'])['id'], $importSchema
			);
		} else {
			return $this->createComponent($importSchema);
		}
	}

	protected function selectComponentGroup($importSchema)
	{
		$componentGroups = ComponentGroups::make()->all()->getComponentGroups();

		$componentGroups->prepend([
			'name' => '<fg=green>Root group</>',
		])->prepend([
			'name' => '<fg=green>New group</>',
		]);

		$componentGroupName = strip_tags($this->choice(
			'Add component to group',
			$componentGroups->pluck('name')->toArray()
		));

		if ($componentGroupName === 'New group') {
			$componentGroupName = $this->createComponentGroup();
		}

		if ($componentGroupName === 'Root group') {
			$importSchema['component_group_uuid'] = null;

			return $importSchema;
		}

		$componentGroups = ComponentGroups::make()->all()->getComponentGroups();

		$group = $componentGroups->filter(fn($group) => $group['name'] === $componentGroupName)->first();

		$importSchema['component_group_uuid'] = $group['uuid'];

		return $importSchema;
	}

	protected function setComponentGroup($importSchema)
	{
		$componentGroups = ComponentGroups::make()->all()->getComponentGroups();

		if (Str::isUuid($this->option('group'))) {
			$group = $componentGroups->filter(fn($group) => $group['uuid'] === $this->option('group'))->first();
		} else if (is_numeric($this->option('group'))) {
			$group = $componentGroups->filter(fn($group) => $group['id'] === (int) $this->option('group'))->first();
		} else {
			$group = $componentGroups->filter(fn($group) => $group['name'] === $this->option('group'))->first();
		}

		if (!$group) {
			$this->error('Component group not found');
			exit;
		}

		$importSchema['component_group_uuid'] = $group['uuid'];

		return $importSchema;
	}

	protected function createComponentGroup()
	{
		$componentGroupName = $this->ask('Enter new group name');

		$componentGroups = ComponentGroups::make()->create([
			'component_group' => [
				'name' => $componentGroupName,
			]
		]);

		return $componentGroupName;
	}

	/**
	 * @param $component
	 * @param $importSchema
	 * @return void
	 */
	protected function updateComponent($componentId, $importSchema)
	{
		$this->warn('Component already exists: ' . $importSchema['name']);
		$this->line('Use --as={name} to import as a new component');

		$this->call('ls:diff-component', [
			'component' => Str::of($this->argument('file'))->before('.json'),
		]);

		if ($this->confirm('Do you want to update the component in Storyblok?')) {
			// TODO - add option to backup existing schema

			$componentGroups = Components::make()->update($componentId, [
				'component' => $importSchema
			]);

			$this->info('Component updated: ' . $importSchema['name']);
		} else {
			$this->info('Component not imported.');
			exit;
		}
	}

	/**
	 * @param $importSchema
	 * @return mixed
	 */
	protected function createComponent($importSchema)
	{
		$componentGroups = Components::make()->create([
			'component' => $importSchema
		]);

		$this->info('Component created: ' . $importSchema['name']);
	}
}
