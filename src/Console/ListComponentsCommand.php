<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\ReadsComponents;
use Riclep\StoryblokCli\Traits\GetsComponents;
use Storyblok\ManagementClient;

class ListComponentsCommand extends Command
{
	use GetsComponents;

    /**
     * @var string
     */
    protected $signature = 'ls:list-components
	            {--additional-fields= : Additional fields to request from Storyblok Management API}
	            {--G|grouped : Include component groups}';

    /**
     * @var string
     */
    protected $description = 'List all Storyblok components for the space.';


	/**
	 * @var ManagementClient
	 */
	protected ManagementClient $managementClient;

	protected ReadsComponents $componentReader;


	public function __construct(ReadsComponents $ReadsComponents)
	{
		parent::__construct();

		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
		$this->componentReader = $ReadsComponents;
	}

    /**
     * Lists all the components in the space
     *
     * @return void
     */
    public function handle()
    {
	    $this->componentReader->requestAll();

        $additionalFields = $this->option('additional-fields') ?
            Str::of($this->option('additional-fields'))->explode(',')
            : collect();

        $rows = $this->componentReader->components()->map(function ($component) use ($additionalFields) {
            $mapped = [
                'id' => $component['id'],
                'name' => $component['name'],
                'display_name' => $component['display_name'],
            ];

			if ($this->option('grouped')) {
				$group = $this->componentReader->findGroup($component['component_group_uuid']);

				if ($group) {
					$mapped['group'] = $group['name'];
					$mapped['group_id'] = $group['id'];
					$mapped['group_uuid'] = $group['uuid'];
				} else {
					$mapped['group'] = '<fg=blue>Root</>';
					$mapped['group_id'] = '';
					$mapped['group_uuid'] = '';
				}
			}

            $mappedAdditional = collect($component)->only($additionalFields);

            return array_merge($mapped, $mappedAdditional->toArray());
        });

	    if ($this->option('grouped')) {
		    $rows = $rows->sortBy('group');
	    }

        $this->table(
            array_keys($rows->first()),
            $rows
        );
    }
}
