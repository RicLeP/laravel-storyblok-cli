<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\Endpoints\Components;

class ComponentListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:component-list
                {--space_id= : Space ID}
	            {--additional-fields= : Additional fields to pull form Storyblok Management API}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Storyblok components for the space.';

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

        $rows = $componentsData->getComponents()->map(function ($c) use ($additionalFields) {
            $mapped = [
                'name' => $c['name'],
                'display_name' => $c['display_name'],
                'has_image' => $c['image'] ? '<fg=green>true</>' : '<fg=red>false</>',
                'has_template' => $c['preview_tmpl'] ? '<fg=green>true</>' : '<fg=red>false</>',
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
