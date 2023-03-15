<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\SpacesData;

class Spaces extends BasicEndpoint
{
    public function __construct($token)
    {
        parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

	public function byId($spaceId): SpacesData
	{
		return new SpacesData(
			$this->client->get('spaces/' . $spaceId . '/')->getBody()
		);
	}

    public function all(): SpacesData
    {
        return new SpacesData(
            $this->client->get('spaces/')->getBody()
        );
    }
}
