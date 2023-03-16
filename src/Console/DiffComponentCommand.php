<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\Endpoints\Components;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

class DiffComponentCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ls:diff-component {component}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Diff components from JSON definitions';

	protected string $path = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

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
		//// TODO validate component JSON

		if (!$this->argument('component')) {
			$this->error('No component specified');
			exit;
		}

		$this->diff($this->getLocalComponent(), $this->getRemoteComponent());

		return Command::SUCCESS;
	}

	protected function getLocalComponent() {
		$path = $this->path . $this->argument('component') . '.json';

		if (!Storage::exists($path)) {
			$this->error('Component file not found: ' . $path);
			exit;
		}

		$localComponent = json_decode(Storage::get($path), true, 512, JSON_THROW_ON_ERROR);
		unset($localComponent['created_at'], $localComponent['updated_at'], $localComponent['component_group_uuid']);

		return $localComponent;
	}

	protected function getRemoteComponent() {
		$components = Components::make()->all()->getComponents();

		$remoteComponent = $components->firstWhere('name', $this->argument('component'));
		unset($remoteComponent['created_at'], $remoteComponent['updated_at'], $remoteComponent['component_group_uuid']);

		return $remoteComponent;
	}

	/**
	 * @param $importComponent
	 * @return void
	 * @throws JsonException
	 */
	protected function diff($localComponent, $remoteComponent)
	{
		$treeWalker = new \TreeWalker(['returntype' => 'array']);
		$changes = $treeWalker->getdiff(
			json_encode($localComponent, JSON_THROW_ON_ERROR),
			json_encode($remoteComponent, JSON_THROW_ON_ERROR)
		);

		if (empty(array_filter($changes))) {
			$this->info('Local and remote schemas are identical');

			return false;
		}

		$this->line('');
		$this->info('Changes found:');

		dump($changes);
	}
}
