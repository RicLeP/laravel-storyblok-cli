<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\ComponentsData;
use Storyblok\ManagementClient;

class Components
{
    private ManagementClient $client;

    private string $spaceId;

    public function __construct($token)
    {
        $this->client = new ManagementClient($token);
    }

    public static function make($token = null): self
    {
        $token = is_null($token) ? config('storyblok-cli.oauth_token') : $token;

        return new self($token);
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
