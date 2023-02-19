<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\ComponentsData;

class Components extends BasicEndpoint
{
    private string $spaceId;

    public function __construct($token)
    {
        parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

    public function all(): ComponentsData
    {
        return new ComponentsData(
            $this->client->get('spaces/'.$this->spaceId.'/components/')->getBody()
        );
    }

    public function spaceId($spaceId): self
    {
        $this->spaceId = $spaceId;

        return $this;
    }
}
