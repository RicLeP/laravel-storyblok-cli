<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Riclep\StoryblokCli\ReadsComponents;
use Riclep\StoryblokCli\WritesComponentJson;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

class ImportComponentCommand extends Command
{
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

	protected $componentReader;


	public function __construct(ReadsComponents $ReadsComponents)
	{

		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
		$this->componentReader = $ReadsComponents;

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
	 * @throws JsonException|ApiException
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
			$componentWriter->group($this->selectComponentGroup($this->componentReader->find($componentWriter->getName())['component_group_uuid']));
		}

		if ($component = $this->componentReader->find($componentWriter->getName())) {
			return $this->updateComponent(
				$component['id'], $componentWriter->toArray()
			);
		} else {
			return $this->createComponent($componentWriter->toArray());
		}
	}

	protected function selectComponentGroup($existingGroupUuid)
	{
		$componentGroups = clone $this->componentReader->groups();

		// TODO print the group it’s already in

		$componentGroups->prepend([
			'name' => '<fg=green>Root group</>',
		])->prepend([
			'name' => '<fg=green>New group</>',
		])->prepend([
			'name' => '<fg=green>Keep group</>',
		]);

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

	protected function getComponentGroup()
	{
		if (Str::isUuid($this->option('group'))) {
			$group = $this->componentReader->groups()->filter(fn($group) => $group['uuid'] === $this->option('group'))->first();
		} else {
			$group = $this->componentReader->groups()->filter(fn($group) => $group['id'] === (int) $this->option('group'))->first();
		}

		if (!$group) {
			$this->error('Component group not found');
			exit;
		}

		return $group['uuid'];
	}

	protected function createComponentGroup()
	{
		$componentGroupName = $this->ask('Enter new group name');

		$this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/component_groups',
			[
				'component_group' => [
					'name' => $componentGroupName,
				]
			])->getBody();

		$this->componentReader->requestAll();

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
			'file' => $this->argument('file'),
			'remote' => $importSchema['name'],
		]);

		if ($this->confirm('Do you want to update the component in Storyblok?')) {
			// TODO - add option to backup existing schema

			$this->managementClient->put('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId,
				[
					'component' => $importSchema
				])->getBody();

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
		$this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/components/', [
			'component' => $importSchema
		])->getBody();

		$this->info('Component created: ' . $importSchema['name']);
	}
}
