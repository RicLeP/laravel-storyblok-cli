<?php

namespace Riclep\StoryblokCli\Exporters;

use Illuminate\Support\Str;

class ComponentExporter extends BasicExporter
{
	protected string $path = 'storyblok' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR;

	/**
	 * Creates the filename for the story.
	 *
	 * @return string
	 */
	protected function guessFilename() {
		return Str::of($this->data['name'])->replace('/', '-')->slug() . '.json';
	}
}