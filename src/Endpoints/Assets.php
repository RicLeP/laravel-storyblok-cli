<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\AssetsData;

class Assets extends BasicEndpoint
{
    public function __construct($token)
    {
	    parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

    public function byId($assetId): AssetsData
    {
        return new AssetsData(
            $this->client->get('spaces/'.$this->spaceId.'/assets/' . $assetId)->getBody()
        );
    }

    public function all(): AssetsData
    {
        return new AssetsData(
            $this->client->get('spaces/'.$this->spaceId.'/assets/')->getBody()
        );
    }
}
