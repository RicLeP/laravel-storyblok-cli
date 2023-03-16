<?php

namespace Riclep\StoryblokCli\Exporters;

use Illuminate\Support\Str;

class StoryExporter extends BasicExporter
{
	protected string $path = 'storyblok' . DIRECTORY_SEPARATOR . 'stories' . DIRECTORY_SEPARATOR;

	/**
	 * Creates the filename for the story.
	 *
	 * @return string
	 */
	protected function guessFilename() {
		return Str::of($this->data['full_slug'])->replace('/', '-')->slug() . '.json';
	}
}