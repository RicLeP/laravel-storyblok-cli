<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class ComponentsData extends BasicData
{
	public function getComponent(): Collection {
		return collect($this->response['component']);
	}

    public function getComponents(): Collection
    {
        return $this->getCollectionFromResponse('components');
    }

    public function getComponentGroups(): Collection
    {
        return $this->getCollectionFromResponse('component_groups');
    }
}
