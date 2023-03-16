<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\Data\ComponentsData;
use Riclep\StoryblokCli\Endpoints\Components;
use Riclep\StoryblokCli\Exporters\ComponentExporter;
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


	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 * @throws JsonException
	 */
    public function handle(): int
    {
	    $components = Components::make()->all()->getComponents();

	    if ($this->option('all')) {
		    $this->exportAllComponents($components);
	    } else {
		    $selectedComponent = $this->argument('component') ?: $this->selectComponent($components);

		    $this->exportComponent($components, $selectedComponent);
	    }

	    return Command::SUCCESS;
    }

	protected function selectComponent(Collection $components) {
		return $this->choice(
			'Select components to export',
			$components->pluck('name')->toArray()
		);
	}


	/**
	 * @throws JsonException
	 */
	protected function exportComponent($components, $componentName, $overwrite = false)
	{
		$component = $components->filter(fn($value) => $value['name'] === $componentName)->first();

		if (!$component) {
			$this->error('Component ' . $componentName . ' not found');
			exit;
		}

		$componentExporter = new ComponentExporter($component);

		if ($componentExporter->exists() && !$overwrite) {
			if (!$this->confirm($componentExporter->getFilename() . ' already exists. Do you want to overwrite it?')) {
				$this->info('Component not exported.');
				exit;
			}
		}

		if ($componentExporter->save()) {
			$this->info('Component exported to storage: ' . $componentExporter->getPath() . $componentExporter->getFilename());
		} else {
			$this->error('Component not exported.');
		}
	}

	protected function exportAllComponents($components)
	{
		if ($this->confirm('This will overwrite previously exported components. Do you want to continue?')) {
			$components->each(fn ($component) => $this->exportComponent($components, $component['name'], true));
		}
	}
}
