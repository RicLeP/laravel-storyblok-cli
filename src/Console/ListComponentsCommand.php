<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\Endpoints\Components;

class ListComponentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:list-components
                {--space_id= : Space ID}
	            {--additional-fields= : Additional fields to request from the Storyblok Management API}
	            {--G|grouped : Include component groups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Storyblok components for a space.';

    public function __construct()
    {
        parent::__construct();
    }

    protected function getOptionWithFallbacks(string $key, $default = '')
    {
        $domain = 'storyblok-cli';
        $key = Str::lower($key);

        return $this->option($key)
            ?? config($domain.'.'.$key)
            ?? $_ENV[Str::upper($domain).'_'.Str::upper($key)]
            ?? $default;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
	    $spaceId = $this->getOptionWithFallbacks('space_id');
	    $componentsData = Components::make()
		    ->spaceId($spaceId)
		    ->all();

	    $additionalFields = $this->option('additional-fields') ?
		    Str::of($this->option('additional-fields'))->explode(',')
		    : collect();

	    $rows = $componentsData->getComponents()->map(function ($component) use ($additionalFields, $componentsData) {
		    $mapped = [
			    'id' => $component['id'],
			    'name' => $component['name'],
			    'display_name' => $component['display_name'],
		    ];

		    if ($this->option('grouped')) {
			    $group = $componentsData->findGroup($component['component_group_uuid']);

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
