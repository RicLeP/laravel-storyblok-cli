<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storyblok\ManagementClient;

// TODO - export and import all components by their group

class CreatesComponents
{

	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	public function create($component) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/components/', [
			'component' => $component
		]);
	}

	public function update($componentId, $component) {
		return $this->managementClient->put('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId,
			[
				'component' => $component
			]
		);
	}
}