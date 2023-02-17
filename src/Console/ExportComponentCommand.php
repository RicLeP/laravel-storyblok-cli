<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\ReadsComponents;
use Storyblok\ManagementClient;

class ExportComponentCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ls:export-component {component?} {--all}';

    /**
     * @var string
     */
    protected $description = 'Export the JSON for components';

	/**
	 * @var string
	 */
	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

	/**
	 * Storyblok Management API Client
	 *
	 * @var ManagementClient
	 */
	protected ManagementClient $managementClient;

	/**
	 * @var ReadsComponents
	 */
	protected $componentReader;

	public function __construct(ReadsComponents $ReadsComponents)
	{
		parent::__construct();

		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
		$this->componentReader = $ReadsComponents;
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 * @throws JsonException
	 */
    public function handle(): int
    {
		$this->componentReader->requestAll();

		if ($this->option('all')) {
			$this->exportAllComponents();
		} else {
			$selectedComponent = $this->argument('component') ?: $this->selectComponent();

			$this->exportComponent($selectedComponent);
		}

        return Command::SUCCESS;
    }

	/**
	 * Ask the user to select a component from the list
	 *
	 * @return array|string
	 */
	protected function selectComponent() {
		return $this->choice(
			'Select component to export',
			$this->componentReader->listByName()->toArray()
		);
	}

	/**
	 * Save the component JSON to storage
	 *
	 * @param $componentName
	 * @return \Closure|void|null
	 * @throws JsonException
	 */
	protected function exportComponent($componentName)
	{
		$component = $this->componentReader->find($componentName);

		if (Storage::exists($this->storagePath . $componentName . '.json') && !$this->option('all')) {
			if (!$this->confirm($componentName . '.json already exists. Do you want to overwrite it?')) {
				$this->info('Component not exported.');
				exit;
			}
		}
// TODO move to class
		Storage::put($this->storagePath . $componentName . '.json', json_encode($component, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

		$this->info('Saved to storage: ' . $componentName . '.json');

		return $component;
	}

	/**
	 * Exports all components to storage
	 *
	 * @return void
	 * @throws JsonException
	 */
	protected function exportAllComponents()
	{
		if ($this->confirm('This will overwrite previously exported components. Do you want to continue?')) {
			$this->componentReader->components()->each(fn($component) => $this->exportComponent($component['name']));
		}
	}
}
