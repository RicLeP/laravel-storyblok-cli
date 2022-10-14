<?php

namespace Riclep\StoryblokCli\Traits;

use Illuminate\Support\Collection;

trait GetsComponents
{
	protected Collection $sbComponents;
	protected Collection $sbComponentGroups;

	protected function requestComponents()
	{
		$response = $this->managementClient->get('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/')->getBody();

		$this->sbComponents = collect($response['components']);
		$this->componentGroups = collect($response['component_groups']);
	}

	protected function requestComponent($componentId)
	{
		$response = $this->managementClient->get('spaces/' . env('STORYBLOK_SPACE_ID') . '/components/' . $componentId)->getBody();

		return $response['component'];
	}
}