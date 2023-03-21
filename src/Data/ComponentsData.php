<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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

		return collect($this->response['component_groups'])->filter(fn($group) => $group[$key] === $needle)->first();
	}
}
