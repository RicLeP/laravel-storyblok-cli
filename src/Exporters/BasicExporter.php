<?php

namespace Riclep\StoryblokCli\Exporters;

use Illuminate\Support\Facades\Storage;

class BasicExporter
{
	protected array $data;
	protected string $filename;

	/**
	 * @param $data
	 */
	public function __construct($data, $filename = null, $path = null)
	{
		$this->data = $data;
		$this->filename = $filename ?? $this->guessFilename();
		$this->path = $path ?? $this->path;
	}

	/**
	 * Saves the story as a JSON file.
	 *
	 * @param $path
	 * @return bool
	 */
	public function save() {
		$json = json_encode($this->data, JSON_PRETTY_PRINT);

		return Storage::put($this->path . $this->filename, $json);
	}

	/**
	 * Checks if JSON for the story exists
	 *
	 * @param $path
	 * @return bool
	 */
	public function exists() {
		return Storage::exists($this->path . $this->filename);
	}

	public function getPath() {
		return $this->path;
	}

	public function getFilename() {
		return $this->filename;
	}
}