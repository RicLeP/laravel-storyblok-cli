<?php

namespace Riclep\StoryblokCli\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use Riclep\StoryblokCli\Endpoints\Components;
use Storyblok\ApiException;

class MergeComponentFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ls:merge-component
        {component : Component name in Storyblok}
        {file : JSON file relative to storyblok/components/}
        {--dry : Show summary only, do not update}
        {--backup= : Backup current remote schema to this path before update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge fields from a JSON schema into an existing Storyblok component';

    /**
     * Base storage path to component schemas.
     */
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
        $name = (string)$this->argument('component');
        $file = (string)$this->argument('file');

        if ($name === '') {
            $this->error('No component name specified');
            return Command::INVALID;
        }

        if ($file === '') {
            $this->error('No JSON file specified');
            return Command::INVALID;
        }

        $fullPath = $this->path . $file;
        if (!Storage::exists($fullPath)) {
            $this->error('Schema file not found: ' . $file);
            return Command::FAILURE;
        }

        // Find remote component by name
        $components = Components::make()->all()->getComponents();
        $existing = $components->firstWhere('name', $name);
        if (!$existing) {
            $this->error('Component not found in Storyblok: ' . $name);
            return Command::FAILURE;
        }

        $componentId = $existing['id'];

        // Ensure we have the latest full schema
        $remoteResponse = Components::make()->byId($componentId);
        $remoteSchema = $remoteResponse->getComponent();
        if (!$remoteSchema) {
            $this->error('Unable to load full component data for: ' . $name);
            return Command::FAILURE;
        }

        // Load incoming
        $incoming = json_decode(Storage::get($fullPath), true, 512, JSON_THROW_ON_ERROR);

        $incomingFields = $this->extractIncomingFields($incoming);
        if ($incomingFields === null) {
            $this->error('No fields found. JSON must contain a "schema" object.');
            return Command::INVALID;
        }

        // Normalise both sides to list of fields with "key"
        $remoteFields = $this->normalizeFieldsToArray($remoteSchema['schema'] ?? []);
        $incomingFields = $this->normalizeFieldsToArray($incomingFields);

        // Merge
        [$mergedFields, $changes] = $this->mergeFieldsByKey($remoteFields, $incomingFields);

        // Prepare updated schema payload (Storyblok expects associative "schema" keyed by field key)
        $updatedSchema = $remoteSchema;
        $updatedSchema['schema'] = $this->arrayFieldsToAssocSchema($mergedFields);

        // Summary
        $this->line('Merge summary for component: ' . $name);
        $this->line('- Updated fields: ' . count($changes['updated']));
        $this->line('- Added fields: ' . count($changes['added']));
        $this->line('- Unchanged fields: ' . count($changes['unchanged']));

        if ($this->option('dry')) {
            $this->warn('Dry run: no changes sent to Storyblok.');
            return Command::SUCCESS;
        }

        // Optional backup
        $backupPath = $this->option('backup');
        if ($backupPath === null && $this->confirm('Backup current remote schema before update?', true)) {
            $backupPath = $this->defaultBackupPath($name);
        }
        if ($backupPath) {
            $this->backupSchema($backupPath, $remoteSchema);
            $this->info('Backup saved: ' . $backupPath);
        }

        if (!$this->confirm('Apply merge to Storyblok?', true)) {
            $this->info('Aborted.');
            return Command::SUCCESS;
        }

        Components::make()->update($componentId, [
            'component' => $updatedSchema,
        ]);

        $this->info('Component updated: ' . $name);
        return Command::SUCCESS;
    }

    /**
     * Extract incoming fields only from "schema".
     *
     * @param array $incoming
     * @return array|null
     */
    protected function extractIncomingFields(array $incoming): ?array
    {
        if (isset($incoming['schema']) && is_array($incoming['schema'])) {
            return $incoming['schema'];
        }

        if (isset($incoming['component']['schema']) && is_array($incoming['component']['schema'])) {
            return $incoming['component']['schema'];
        }

        return null;
    }

    /**
     * Normalize fields structure into a sequential array of fields containing "key".
     *
     * @param array $fields
     * @return array
     */
    protected function normalizeFieldsToArray(array $fields): array
    {
        if ($this->isAssoc($fields)) {
            $out = [];
            foreach ($fields as $key => $field) {
                if (is_array($field)) {
                    $field['key'] = $field['key'] ?? $key;
                }
                $out[] = $field;
            }
            return $out;
        }

        return $fields;
    }

    /**
     * Convert array-list fields to associative "schema" map keyed by "key".
     *
     * @param array $fields
     * @return array
     */
    protected function arrayFieldsToAssocSchema(array $fields): array
    {
        $schema = [];
        foreach ($fields as $field) {
            if (!isset($field['key'])) {
                continue;
            }
            $copy = $field;
            unset($copy['key']);
            $schema[$field['key']] = $copy;
        }
        return $schema;
    }

    /**
     * Merge incoming into existing by 'key'.
     *
     * @param array $existing
     * @param array $incoming
     * @return array{0: array, 1: array}
     */
    protected function mergeFieldsByKey(array $existing, array $incoming): array
    {
        $byKey = [];
        foreach ($existing as $f) {
            if (isset($f['key'])) {
                $byKey[$f['key']] = $f;
            }
        }

        $changes = [
            'updated' => [],
            'added' => [],
            'unchanged' => [],
        ];

        foreach ($incoming as $nf) {
            if (!isset($nf['key'])) {
                continue;
            }
            $k = $nf['key'];
            if (isset($byKey[$k])) {
                $merged = $this->shallowMerge($byKey[$k], $nf);
                if ($merged !== $byKey[$k]) {
                    $byKey[$k] = $merged;
                    $changes['updated'][] = $k;
                } else {
                    $changes['unchanged'][] = $k;
                }
            } else {
                $byKey[$k] = $nf;
                $changes['added'][] = $k;
            }
        }

        // Preserve original order, append new keys at end
        $ordered = [];
        $existingKeys = array_map(fn($f) => $f['key'] ?? null, $existing);
        foreach ($existingKeys as $k) {
            if ($k !== null && isset($byKey[$k])) {
                $ordered[] = $byKey[$k];
                unset($byKey[$k]);
            }
        }
        foreach ($byKey as $f) {
            $ordered[] = $f;
        }

        return [$ordered, $changes];
    }

    /**
     * Non-recursive merge: incoming overrides scalar values and sub-arrays entirely.
     *
     * @param array $base
     * @param array $over
     * @return array
     */
    protected function shallowMerge(array $base, array $over): array
    {
        foreach ($over as $k => $v) {
            $base[$k] = $v;
        }
        return $base;
    }

    protected function isAssoc(array $a): bool
    {
        if ([] === $a) {
            return false;
        }
        return array_keys($a) !== range(0, count($a) - 1);
    }

    protected function defaultBackupPath(string $name): string
    {
        $stamp = date('Ymd_His');
        $file = $name . '-' . $stamp . '.json';
        return $this->path . 'backups' . DIRECTORY_SEPARATOR . $file;
    }

    protected function backupSchema(string $path, Collection $schema): void
    {
        $dir = Str::beforeLast($path, DIRECTORY_SEPARATOR);
        if ($dir && !Storage::exists($dir)) {
            Storage::makeDirectory($dir);
        }
        Storage::put($path, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
