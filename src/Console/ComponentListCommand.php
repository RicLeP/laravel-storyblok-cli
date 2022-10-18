<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\Traits\GetsComponents;
use Storyblok\ManagementClient;

class ComponentListCommand extends Command
{
	use GetsComponents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:component-list
	            {--additional-fields= : Additional fields to pull form Storyblok Management API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Storyblok components for the space.';


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
     * @return void
     */
    public function handle()
    {
	    $this->requestComponents();

        $additionalFields = $this->option('additional-fields') ?
            Str::of($this->option('additional-fields'))->explode(',')
            : collect();

        $rows = $this->sbComponents->map(function ($c) use ($additionalFields) {
            $mapped = [
                'name' => $c['name'],
                'display_name' => $c['display_name'],
                'has_image' => $c['image'] ? "<fg=green>true</>" : '<fg=red>false</>',
                'has_template' => $c['preview_tmpl'] ? "<fg=green>true</>" : '<fg=red>false</>',
            ];

            $mappedAdditional = collect($c)->only($additionalFields);

            return array_merge($mapped, $mappedAdditional->toArray());
        });

        $this->table(
            array_keys($rows->first()),
            $rows
        );
    }

}
