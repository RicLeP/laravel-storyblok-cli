<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\ComponentsData;

class Components extends BasicEndpoint
{
    public function __construct($token)
    {
	    parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

	public function byId($componentId): ComponentsData
	{
		return new ComponentsData(
			$this->client->get('spaces/'.$this->spaceId.'/components/' . $componentId)->getBody()
		);
	}

    public function all(): ComponentsData
    {
        return new ComponentsData(
            $this->client->get('spaces/'.$this->spaceId.'/components/')->getBody()
        );
    }

	public function create($component): ComponentsData {
		return new ComponentsData(
			$this->client->post('spaces/'.$this->spaceId.'/components/', $component)->getBody()
		);
	}

	public function update($id, $component): ComponentsData {
		return new ComponentsData(
			$this->client->put('spaces/'.$this->spaceId.'/components/' . $id, $component)->getBody()
		);
	}

	public function delete($id) {
		return new ComponentsData(
			$this->client->delete('spaces/'.$this->spaceId.'/components/' . $id)->getBody()
		);
	}
}
