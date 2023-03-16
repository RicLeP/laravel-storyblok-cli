<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Riclep\StoryblokCli\Endpoints\Spaces;

class SpaceListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:space-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the Storyblok spaces.';

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
        $spacesData = Spaces::make()->all();

        $rows = $spacesData->getSpaces()->map(function ($c) {
            return  [
                'name' => Str::limit($c['name'], config('storyblok-cli.str_limit_chars', 40), '...'),
                'id' => $c['id'],
                'plan' => $c['plan'],
                'region' => $c['region'],
            ];
        });

        $this->table(
            array_keys($rows->first()),
            $rows
        );
    }
}
