<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

class DiffComponentCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'ls:diff-component {file} {remote?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Diff components from JSON definitions';

	protected $storagePath = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

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

		$this->requestComponents();

		$this->diff($this->storagePath . $this->argument('file'), $this->argument('remote'));

		return Command::SUCCESS;
	}

	/**
	 * @param $importSchema
	 * @return void
	 * @throws JsonException
	 */
	protected function diff($localComponent, $remoteComponent = null)
	{
		$localSchema = $this->cleanSchema(json_decode(Storage::get($localComponent), true, 512, JSON_THROW_ON_ERROR));

		$remoteSchema = $this->cleanSchema($this->requestComponent($this->sbComponents->firstWhere('name', $remoteComponent ?? $localSchema['name'])['id']));

		$treeWalker = new \TreeWalker(['returntype' => 'array']);
		$changes = $treeWalker->getdiff(
			json_encode($localSchema, JSON_THROW_ON_ERROR),
			json_encode($remoteSchema, JSON_THROW_ON_ERROR)
		);

		if (empty(array_filter($changes))) {
			$this->info('Local and remote schemas are identical');

			return false;
		}

		$this->line('');
		$this->info('Differences in remote schema:');

		dump($changes);
	}

	protected function cleanSchema($schema)
	{
		unset($schema['id'],$schema['created_at'], $schema['updated_at'], $schema['component_group_uuid'], $schema['name'], $schema['real_name']);

		return $schema;
	}
}
