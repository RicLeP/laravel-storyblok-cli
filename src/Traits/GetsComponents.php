<?php

namespace Riclep\StoryblokCli\Traits;

use Illuminate\Support\Collection;

trait GetsComponents
{
	protected Collection $sbComponents;
	protected Collection $sbComponentGroups;

	protected function requestComponents()
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/')->getBody();

		$this->sbComponents = collect($response['components']);
		$this->sbComponentGroups = collect($response['component_groups']);
	}

	protected function requestComponent($componentId)
	{
		$response = $this->managementClient->get('spaces/' . config('storyblok-cli.space_id') . '/components/' . $componentId)->getBody();

		return $response['component'];
	}
}