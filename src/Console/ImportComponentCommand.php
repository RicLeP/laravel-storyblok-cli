<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Riclep\StoryblokCli\CreatesComponentGroups;
use Riclep\StoryblokCli\CreatesComponents;
use Riclep\StoryblokCli\ReadsComponents;
use Riclep\StoryblokCli\WritesComponentJson;
use Storyblok\ApiException;

class ImportComponentCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ls:import-component {file?} {--as=} {--group=false}';

	/**
	 * @var string
	 */
	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import components from JSON definitions';

	/**
	 * @var ReadsComponents
	 */
	protected $componentReader;

	/**
	 * @var CreatesComponents
	 */
	protected $componentCreator;


	/**
	 * @param ReadsComponents $readsComponents
	 * @param CreatesComponents $createsComponents
	 */
	public function __construct(ReadsComponents $readsComponents, CreatesComponents $createsComponents)
	{
		parent::__construct();

		$this->componentReader = $readsComponents;
		$this->componentCreator = $createsComponents;
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

		$this->componentReader->requestAll();

	    $this->importComponent(new WritesComponentJson(Storage::get($this->storagePath . $this->argument('file'))));

	    return Command::SUCCESS;
    }

	/**
	 * Imports a component from JSON
	 *
	 * @param WritesComponentJson $componentWriter
	 * @return mixed|null
	 */
	protected function importComponent(WritesComponentJson $componentWriter)
	{
		if ($this->option('as')) {
			$componentWriter->name($this->option('as'));
		}

		// don’t like this but not sure how to have a default value which prompts for a choice and
		// is skipped when now passed as an option
		if ($this->option('group') && $this->option('group') !== 'false') {
			$componentWriter->group($this->getComponentGroup());
		} else {
			$existingGroup = $this->componentReader->find($componentWriter->getName());

			if ($existingGroup) {
				$componentWriter->group($this->selectComponentGroup($existingGroup['component_group_uuid']));
			} else {
				$componentWriter->group($this->selectComponentGroup());
			}
		}

		if ($component = $this->componentReader->find($componentWriter->getName())) {
			return $this->updateComponent(
				$component['id'], $componentWriter->toArray()
			);
		} else {
			return $this->createComponent($componentWriter->toArray());
		}
	}

	/**
	 * Select the component group to add the component to
	 *
	 * @param $existingGroupUuid
	 * @return mixed|null
	 */
	protected function selectComponentGroup($existingGroupUuid = null)
	{
		$componentGroups = clone $this->componentReader->groups();

		$componentGroups->prepend([
			'name' => '<fg=green>Root group</>',
		])->prepend([
			'name' => '<fg=green>New group</>',
		]);

		if ($existingGroupUuid) {
			$componentGroups->prepend([
				'name' => '<fg=green>Keep group</>',
			]);
		}

		$componentGroupName = strip_tags($this->choice(
			'Add component to group',
			$componentGroups->pluck('name')->toArray()
		));

		// TODO - keep UUID from JSON if compontent is not in Storyblok already
		if ($componentGroupName === 'Keep group') {
			return $existingGroupUuid;
		}

		if ($componentGroupName === 'New group') {
			$componentGroupName = $this->createComponentGroup();
		}

		if ($componentGroupName === 'Root group') {
			return null;
		}

		$group = $this->componentReader->groups()->filter(fn($group) => $group['name'] === $componentGroupName)->first();

		return $group['uuid'];
	}

	/**
	 * Determine the component group or action selected by the user
	 *
	 * @return mixed|void
	 */
	protected function getComponentGroup()
	{
		if (Str::isUuid($this->option('group'))) {
			$group = $this->componentReader->groups()->filter(fn($group) => $group['uuid'] === $this->option('group'))->first();
		} elseif (is_numeric($this->option('group'))) {
			$group = $this->componentReader->groups()->filter(fn($group) => $group['id'] === (int) $this->option('group'))->first();
		} else {
			$group = $this->componentReader->groups()->filter(fn($group) => $group['name'] === $this->option('group'))->first();
		}

		if (!$group) {
			$this->error('Component group not found');
			exit;
		}

		return $group['uuid'];
	}

	/**
	 * Creates a new component group
	 *
	 * @return string
	 */
	protected function createComponentGroup()
	{
		$componentGroupName = $this->ask('Enter new group name');

		$componentGroupCreator = new CreatesComponentGroups();
		$componentGroupCreator->create($componentGroupName);

		$this->componentReader->requestAll();

		return $componentGroupName;
	}

	/**
	 * Updates an existing component
	 *
	 * @param $componentId
	 * @param $importSchema
	 * @return void
	 */
	protected function updateComponent($componentId, $importSchema)
	{
		$this->warn('Component already exists: ' . $importSchema['name']);
		$this->line('Use --as={name} to import as a new component');

		$this->call('ls:diff-component', [
			'file' => $this->argument('file'),
			'remote' => $importSchema['name'],
		]);

		if ($this->confirm('Do you want to update the component in Storyblok?')) {
			// TODO - add option to backup existing schema
			$this->componentCreator->update($componentId, $importSchema);

			$this->info('Component updated: ' . $importSchema['name']);
		} else {
			$this->info('Component not imported.');
			exit;
		}
	}

	/**
	 * Creates a new component in Storyblok
	 *
	 * @param $importSchema
	 * @return void
	 */
	protected function createComponent($importSchema)
	{
		$this->componentCreator->create($importSchema);

		$this->info('Component created: ' . $importSchema['name']);
	}
}
