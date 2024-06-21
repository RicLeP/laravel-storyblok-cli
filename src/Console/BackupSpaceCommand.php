<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Riclep\StoryblokCli\Endpoints\AssetFolders;
use Riclep\StoryblokCli\Endpoints\Assets;
use Riclep\StoryblokCli\Endpoints\ComponentGroups;
use Riclep\StoryblokCli\Endpoints\Components;
use Riclep\StoryblokCli\Endpoints\Stories;
use Storyblok\ApiException;

class BackupSpaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:backup {--with-assets} {--output-dir=storyblok-backup} {--zip} {--details}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup a Storyblok space';

    protected string $path = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

    /**
     * Execute the console command.
     *
     * @return int
     * @throws ApiException
     * @throws JsonException
     */
    public function handle(): int
    {
        $this->backupStories();
        $this->backupComponents();
        $this->backupComponentGroups();
        $this->backupAssets();
        $this->backupAssetFolders();

        $zip = $this->option('zip');

        if ($zip) {
            $this->zipFiles($this->option('output-dir'));
        }

        return Command::SUCCESS;
    }

    protected function backupStories() {
        $stories = Stories::make()->all()->getStories();

        foreach ($stories as $story) {
            $storyDetail = Stories::make()->byId($story['id'])->getStory();
            $this->saveJsonToDisk($this->option('output-dir') . '/stories', $story['id'] . '.json', $storyDetail);

            if ($this->option('details')) {
                $this->output->writeln('Backed up story: ' . $story['id']);
            }
        }
    }

    protected function backupComponents() {
        $components = Components::make()->all()->getComponents();

        foreach ($components as $component) {
            $this->saveJsonToDisk($this->option('output-dir') . '/components', $component['id'] . '.json', $component);

            if ($this->option('details')) {
                $this->output->writeln('Backed up component: ' . $component['id']);
            }
        }
    }

    protected function backupComponentGroups() {
        $componentGroups = ComponentGroups::make()->all()->getComponentGroups();

        foreach ($componentGroups as $componentGroup) {
            $this->saveJsonToDisk($this->option('output-dir') . '/component-groups', $componentGroup['id'] . '.json', $componentGroup);

            if ($this->option('details')) {
                $this->output->writeln('Backed up component group: ' . $componentGroup['id']);
            }
        }
    }

    protected function backupAssets() {
        $assets = Assets::make()->all()->getAssets();

        foreach ($assets as $asset) {
            $this->saveJsonToDisk($this->option('output-dir') . '/assets', $asset['id'] . '.json', $asset);

            if ($this->option('details')) {
                $this->output->writeln('Backed up asset json: ' . $asset['id']);
            }

            if ($this->option('with-assets')) {
                $extension = pathinfo($asset['filename'], PATHINFO_EXTENSION);

                $response = Http::get($asset['filename']);

                if($response->successful()) {
                    Storage::disk('local')->put($this->option('output-dir') . '/assets/' . $asset['id'] . '.' . $extension, $response->body());
                } else {
                    $this->output->writeln('Failed to download asset file: ' . $asset['id']);
                }

                if ($this->option('details')) {
                    $this->output->writeln('Backed up asset file: ' . $asset['id']);
                    $this->output->writeln('Asset file URL: ' . $asset['filename']);
                }
            }
        }
    }

    protected function backupAssetFolders() {
        $assetFolders = AssetFolders::make()->all()->getAssetFolders();

        foreach ($assetFolders as $assetFolder) {
            $this->saveJsonToDisk($this->option('output-dir') . '/asset-folders', $assetFolder['id'] . '.json', $assetFolder);

            if ($this->option('details')) {
                $this->output->writeln('Backed up asset folder: ' . $assetFolder['id']);
            }
        }
    }

    protected function saveJsonToDisk($outputDir, $fileName, $data) {
        Storage::disk('local')->put($outputDir . '/' . $fileName, json_encode($data));
    }

    protected function zipFiles($outputDir) {
        $zip = new \ZipArchive();

        $zipFilename = 'backup-' . date('Y-m-d-H-i-s');

        $zipFilePath = storage_path('app' . DIRECTORY_SEPARATOR . $outputDir) . '/' . $zipFilename . '.zip';

        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            exit("Cannot open <$zipFilePath>\n");
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(storage_path('app' . DIRECTORY_SEPARATOR . $outputDir)), \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(storage_path('app' . DIRECTORY_SEPARATOR . $outputDir)) + 1);

                if (pathinfo($filePath, PATHINFO_EXTENSION) === 'zip' && pathinfo($relativePath, PATHINFO_DIRNAME) === '.') {
                    continue;
                }

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        if ($this->option('details')) {
            $this->output->writeln('Created zip file: ' . $zipFilePath);
        }
    }
}
