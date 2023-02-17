<?php

namespace Riclep\StoryblokCli;

use Storyblok\ApiException;
use Storyblok\ManagementClient;

class CreatesStories
{
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Creates stories using the Storyblok Management API.
	 *
	 * @param $story
	 * @return mixed
	 * @throws ApiException
	 */
	public function create($story) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/stories/', $story)->getBody()['story'];
	}
}