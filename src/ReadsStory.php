<?php

namespace Riclep\StoryblokCli;

use Storyblok\ApiException;
use Storyblok\ManagementClient;

class ReadsStory
{
	protected $story;

	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Request a story by ID.
	 *
	 * @param $storyId
	 * @return mixed
	 * @throws \Exception
	 */
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

	/**
	 * Request a story by slug.
	 *
	 * @param $slug
	 * @return mixed
	 * @throws ApiException
	 */
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

	/**
	 * Check if a story exists by slug.
	 *
	 * @param $slug
	 * @return bool
	 * @throws ApiException
	 */
	public function exists($slug) {
		return (boolean) $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/stories/', [
			'with_slug' => $slug
		])->getBody()['stories'];
	}

	/**
	 * Return the story.
	 *
	 * @return mixed
	 */
	public function story()
	{
		return $this->story;
	}
}