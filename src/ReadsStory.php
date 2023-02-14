<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storyblok\ManagementClient;

class ReadsStory
{
	protected $story;
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	public function requestById($storyId)
	{
		try {
			$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/stories/' . $storyId)->getBody();

		} catch (\Exception $e) {
			throw new \Exception('No story found with ID: ' . $storyId);
		}

		return $response;

		// TODO check if folder
	}

	public function requestBySlug($slug)
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/stories/', [
			'with_slug' => $slug
		])->getBody();

		$stories = collect($response['stories']);

		if ($stories->isEmpty()) {
			throw new \Exception('No story found with slug: ' . $slug);
		}

		return $this->requestById($response['stories'][0]['id']);
	}

	public function exists($slug) {
		return (boolean) $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/stories/', [
			'with_slug' => $slug
		])->getBody()['stories'];
	}

	public function story()
	{
		return $this->story;
	}
}