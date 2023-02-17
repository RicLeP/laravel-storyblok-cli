<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SavesStoryJson
{
	/**
	 * @param $story
	 */
	public function __construct($story)
	{
		$this->story = $story;
		$this->filename = $this->filename();
	}

	/**
	 * Saves the story as a JSON file.
	 *
	 * @param $path
	 * @return bool
	 */
	public function save($path) {
		$json = json_encode($this->story, JSON_PRETTY_PRINT);

		return Storage::put($path . $this->filename, $json);
	}

	/**
	 * Creates the filename for the story.
	 *
	 * @return string
	 */
	protected function filename() {
		return Str::of($this->story['story']['full_slug'])->replace('/', '-')->slug() . '.json';
	}

	/**
	 * Checks if the story has already been exported.
	 *
	 * @param $path
	 * @return bool
	 */
	public function exportExists($path) {
		return Storage::exists($path . $this->filename);
	}
}