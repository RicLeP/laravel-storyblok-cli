<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class ComponentGroupsData extends BasicData
{
	public function getComponentGroup(): Collection {
		return collect($this->response['component_group']);
	}

	public function getComponentGroups(): Collection
	{
		return collect($this->response['component_groups']);
	}
}
