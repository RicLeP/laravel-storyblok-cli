<?php

namespace RicLep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\Traits\GetsComponents;
use Storyblok\ManagementClient;

class ExportComponentCommand extends Command
{
	use GetsComponents;

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
	 * @throws JsonException
	 */
    public function handle(): int
    {
		$this->requestComponents();

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
			'Select components to export',
			$this->sbComponents->pluck('name')->toArray()
		);
	}

	/**
	 * @throws JsonException
	 */
	protected function exportComponent($componentName)
	{
		$component = $this->sbComponents->filter(fn($value) => $value['name'] === $componentName)->first();

		if (!$component) {
			$this->error('Component ' . $componentName . ' not found');
			exit;
		}

		if (Storage::exists($componentName . '.json') && !$this->option('all')) {
			if (!$this->confirm($componentName . '.json already exists. Do you want to overwrite it?')) {
				$this->info('Component not exported.');
				exit;
			}
		}

		Storage::put($componentName . '.json', json_encode($component, JSON_THROW_ON_ERROR));

		$this->info($componentName . '.json exported');

		return $component;
	}

	protected function exportAllComponents()
	{
		if ($this->confirm('This will overwrite previously exported components. Do you want to continue?')) {
			$this->sbComponents->each(function ($component) {
				$this->exportComponent($component['name']);
			});
		}
	}
}
