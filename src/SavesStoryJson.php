<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

// TODO - add ability to write json file

class SavesStoryJson
{
	public function __construct($story)
	{
		$this->story = $story;
		$this->filename = $this->filename();
	}

	public function save($path) {
		$json = json_encode($this->story, JSON_PRETTY_PRINT);

		return Storage::put($path . $this->filename, $json);
	}

	protected function filename() {
		return Str::of($this->story['full_slug'])->replace('/', '-')->slug() . '.json';
	}

	public function exportExists($path) {
		return Storage::exists($path . $this->filename);
	}
}