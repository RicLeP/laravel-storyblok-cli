<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\ComponentGroupsData;

class ComponentGroups extends BasicEndpoint
{
    public function __construct($token)
    {
	    parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

	public function byId($componentId): ComponentGroupsData
	{
		return new ComponentGroupsData(
			$this->client->get('spaces/'.$this->spaceId.'/component_groups/' . $componentId)->getBody()
		);
	}

    public function all(): ComponentGroupsData
    {
        return new ComponentGroupsData(
            $this->client->get('spaces/'.$this->spaceId.'/component_groups/')->getBody()
        );
    }

	public function create($component): ComponentGroupsData {
		return new ComponentGroupsData(
			$this->client->post('spaces/'.$this->spaceId.'/component_groups/', $component)->getBody()
		);
	}

	public function update($id, $component): ComponentGroupsData {
		return new ComponentGroupsData(
			$this->client->put('spaces/'.$this->spaceId.'/component_groups/' . $id, $component)->getBody()
		);
	}

	public function delete($id) {
		return new ComponentGroupsData(
			$this->client->delete('spaces/'.$this->spaceId.'/component_groups/' . $id)->getBody()
		);
	}
}
