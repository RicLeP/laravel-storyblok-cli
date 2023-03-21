<?php

namespace Riclep\StoryblokCli\Endpoints;

use Storyblok\ManagementClient;

class BasicEndpoint
{
    protected ManagementClient $client;

	protected string $spaceId;

    public function __construct($token)
    {
        $this->client = new ManagementClient($token);

	    $this->spaceId = config('storyblok-cli.space_id');
    }

    public function mockable($array) {
        $this->client->mockable($array);
    }

    protected static function getToken($token = null)
    {
        return is_null($token) ? config('storyblok-cli.oauth_token') : $token;
    }

	public function spaceId($spaceId): self
	{
		$this->spaceId = $spaceId;

		return $this;
	}
}
