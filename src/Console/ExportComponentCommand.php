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
    protected $signature = 'ls:export-component {component?}';

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

		$this->managementClient = new ManagementClient(env('STORYBLOK_OAUTH_TOKEN'));
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

		$selectedComponent = $this->argument('component') ?: $this->selectComponent();

		$this->exportComponent($selectedComponent);

		$this->info('Component exported successfully: ' . $selectedComponent . '.json');

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

		Storage::put($componentName . '.json', json_encode($component, JSON_THROW_ON_ERROR));

		return $component;
	}
}
