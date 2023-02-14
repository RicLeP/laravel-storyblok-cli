<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storyblok\ManagementClient;

class CreatesStories
{
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	public function create($story) {
		return $this->managementClient->post('spaces/' . config('storyblok-cli.space_id') . '/stories/', $story)->getBody()['story'];
	}
}