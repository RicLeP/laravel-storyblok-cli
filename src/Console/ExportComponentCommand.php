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
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:export-component {component?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the JSON for components';

	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

	/**
	 * @var ManagementClient
	 */
	protected ManagementClient $managementClient;

	protected $components;

	public function __construct(ReadsComponents $ReadsComponents)
	{
		parent::__construct();

		$this->components = $ReadsComponents;

		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 * @throws JsonException
	 */
    public function handle(): int
    {
		$this->components->requestAll();

		if ($this->option('all')) {
			$this->exportAllComponents();
		} else {
			$selectedComponent = $this->argument('component') ?: $this->selectComponent();

			$this->exportComponent($selectedComponent);
		}

        return Command::SUCCESS;
    }

	protected function selectComponent() {
		return $this->choice(
			'Select component to export',
			$this->components->listByName()->toArray()
		);
	}

	/**
	 * @throws JsonException
	 */
	protected function exportComponent($componentName)
	{
		$component = $this->components->selectByName($componentName);

		if (Storage::exists($this->storagePath . $componentName . '.json') && !$this->option('all')) {
			if (!$this->confirm($componentName . '.json already exists. Do you want to overwrite it?')) {
				$this->info('Component not exported.');
				exit;
			}
		}

		Storage::put($this->storagePath . $componentName . '.json', json_encode($component, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

		$this->info('Saved to storage: ' . $componentName . '.json');

		return $component;
	}

	protected function exportAllComponents()
	{
		if ($this->confirm('This will overwrite previously exported components. Do you want to continue?')) {
			$this->components->components()->each(function ($component) {
				$this->exportComponent($component['name']);
			});
		}
	}
}
