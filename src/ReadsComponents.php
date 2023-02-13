<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Storyblok\ManagementClient;

class ReadsComponents
{
	protected Collection $components;
	protected Collection $groups;
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	public function requestAll()
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/')->getBody();

		$this->components = collect($response['components']);
		$this->groups = collect($response['component_groups']);
	}

	// TODO --- does this belong here?
	public function requestById($componentId)
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId)->getBody();

		return $response['component'];
	}

	/**
	 * @return Collection
	 */
	public function components(): Collection
	{
		return $this->components;
	}

	/**
	 * @return Collection
	 */
	public function groups(): Collection
	{
		return $this->groups;
	}

	/**
	 * @return Collection
	 */
	// TODO - retuen UUID ore ID? can an artisan question return that?
	public function listByName(): Collection
	{
		return $this->components->pluck('name');
	}

	// TODO update to use ID or UUID instead?
	public function selectByName($componentName)
	{
		$component = $this->components->filter(fn($value) => $value['name'] === $componentName)->first();

		// TODO - throw exception
		if (!$component) {
			$this->error('Component ' . $componentName . ' not found');
			exit;
		}

		return $component;
	}
}