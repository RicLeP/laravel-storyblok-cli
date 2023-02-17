<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storyblok\ApiException;
use Storyblok\ManagementClient;

// TODO - export and import all components by their group

class ReadsComponents
{
	protected Collection $components;
	protected Collection $groups;
	protected ManagementClient $managementClient;

	public function __construct()
	{
		$this->managementClient = new ManagementClient(config('storyblok-cli.oauth_token'));
	}

	/**
	 * Gets all components and groups from the Storyblok Management API.
	 *
	 * @return void
	 * @throws ApiException
	 */
	public function requestAll()
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/')->getBody();

		$this->components = collect($response['components']);
		$this->groups = collect($response['component_groups']);
	}


	/**
	 * Returns a single component by ID.
	 *
	 * @param $componentId
	 * @return mixed
	 * @throws ApiException
	 */
	public function requestById($componentId)
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId)->getBody();

		return $response['component'];
	}

	/**
	 * Returns a Collection of components.
	 *
	 * @return Collection
	 */
	public function components(): Collection
	{
		return $this->components;
	}

	/**
	 * Returns a Collection of component groups.
	 *
	 * @return Collection
	 */
	public function groups(): Collection
	{
		return $this->groups;
	}

	/**
	 * Returns a Collection of component names.
	 *
	 * @return Collection
	 */
	public function listByName(): Collection
	{
		// TODO - should it return UUID or ID? can an artisan question return that?
		return $this->components->pluck('name');
	}

	/**
	 * Returns a single component by name.
	 *
	 * @return Collection
	 */
	public function find($componentName) {
		// TODO update to use ID or UUID instead?
		return $this->components->filter(fn($component) => $component['name'] === $componentName)->first();
	}

	/**
	 * Find a group by name, ID or UUID.
	 *
	 * @param $needle
	 * @return \Closure|null
	 */
	public function findGroup($needle) {
		if (Str::isUuid($needle)) {
			$key = 'uuid';
		} elseif (is_numeric($needle)) {
			$key = 'id';
		} else {
			$key = 'name';
		}

		return $this->groups->filter(fn($group) => $group[$key] === $needle)->first();
	}
}