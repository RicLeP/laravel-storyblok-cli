<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SavesComponentJson
{
	/**
	 * @param $component
	 */
	public function __construct($component)
	{
		$this->component = $component;
		$this->filename = $this->filename();
	}

	/**
	 * Saves the story as a JSON file.
	 *
	 * @param $path
	 * @return bool
	 */
	public function save($path) {
		$json = json_encode($this->component, JSON_PRETTY_PRINT);

		return Storage::put($path . $this->filename, $json);
	}

	/**
	 * Creates the filename for the story.
	 *
	 * @return string
	 */
	protected function filename() {
		return $this->component['name'] . '.json';
	}

	/**
	 * Checks if JSON for the component exists
	 *
	 * @param $path
	 * @return bool
	 */
	public function exportExists($path) {
		return Storage::exists($path . $this->filename);
	}
}