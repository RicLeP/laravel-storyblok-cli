<?php

namespace Riclep\StoryblokCli\Endpoints;

use Storyblok\ManagementClient;

class BasicEndpoint
{
    protected ManagementClient $client;

    public function __construct($token)
    {
        $this->client = new ManagementClient($token);
    }

    public function mockable($array) {
        $this->client->mockable($array);
    }

    protected static function getToken($token = null)
    {
        return is_null($token) ? config('storyblok-cli.oauth_token') : $token;
    }
}