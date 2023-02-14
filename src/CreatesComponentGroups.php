<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storyblok\ManagementClient;

class CreatesComponentGroups
{
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	public function create($name) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/component_groups',
			[
				'component_group' => [
					'name' => $name,
				]
			]);
	}
}