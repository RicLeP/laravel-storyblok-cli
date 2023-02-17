<?php

namespace Riclep\StoryblokCli;

use Storyblok\ApiException;
use Storyblok\ManagementClient;

class CreatesComponentGroups
{
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Creates component groups using the Storyblok Management API.
	 *
	 * @param $name
	 * @return \stdClass
	 * @throws ApiException
	 */
	public function create($name) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/component_groups',
			[
				'component_group' => [
					'name' => $name,
				]
			]);
	}
}