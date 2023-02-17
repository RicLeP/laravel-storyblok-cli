<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\ReadsComponents;
use Storyblok\ManagementClient;

class DiffComponentCommand extends Command
{
	/**
	 * @var string
	 */
	protected $signature = 'ls:diff-component {file} {remote?}';

	/**
	 * @var string
	 */
	protected $description = 'Diff components from JSON definitions';

	/**
	 *
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

		$this->diff($this->storagePath . $this->argument('file'), $this->argument('remote'));

		return Command::SUCCESS;
	}

	/**
	 * Takes a local component file and compares it to a remote component
	 *
	 * @param $localComponent
	 * @param null $remoteComponent
	 * @return void
	 * @throws JsonException
	 */
	protected function diff($localComponent, $remoteComponent = null)
	{
		$localSchema = json_decode(Storage::get($localComponent), true, 512, JSON_THROW_ON_ERROR);

		$remoteSchema = $this->componentReader->requestById(
			$this->componentReader->find($remoteComponent ?? $localSchema['name'])['id']
		);

		$treeWalker = new \TreeWalker(['returntype' => 'array']);
		$changes = $treeWalker->getdiff(
			json_encode($this->cleanSchema($localSchema), JSON_THROW_ON_ERROR),
			json_encode($this->cleanSchema($remoteSchema), JSON_THROW_ON_ERROR)
		);

		if (empty(array_filter($changes))) {
			$this->info('Local and remote schemas are identical');

			return false;
		}

		$this->line('');
		$this->info('Differences in remote schema:');

		dump($changes);
	}

	/**
	 * Removes fields that we don't care about when diffing
	 *
	 * @param $schema
	 * @return mixed
	 */
	protected function cleanSchema($schema)
	{
		unset($schema['id'], $schema['created_at'], $schema['updated_at'], $schema['component_group_uuid'], $schema['name'], $schema['real_name']);

		return $schema;
	}
}
