<?php

namespace Riclep\StoryblokCli;

use Storyblok\ApiException;
use Storyblok\ManagementClient;

// TODO - export and import all components by their group

class CreatesComponents
{
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Creates components using the Storyblok Management API.
	 *
	 * @param $component
	 * @return \stdClass
	 * @throws ApiException
	 */
	public function create($component) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/components/', [
			'component' => $component
		]);
	}

	/**
	 * Updates components using the Storyblok Management API.
	 *
	 * @param $componentId
	 * @param $component
	 * @return \stdClass
	 * @throws ApiException
	 */
	public function update($componentId, $component) {
		return $this->managementClient->put('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId,
			[
				'component' => $component
			]
		);
	}
}