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
        $response = $this->client->get('spaces/'.$this->spaceId.'/assets');

        $headers = $response->getHeaders();
        $perPage = $headers['Per-Page'][0];
        $total = $headers['Total'][0];

        $assets = $response->getBody()['assets'];

        if ($perPage < $total) {
            $pages = ceil($total / $perPage);

            for ($i = 2; $i <= $pages; $i++) {
                $response = $this->client->get('spaces/'.$this->spaceId.'/assets', [
                    'page' => $i
                ]);

                $assets = array_merge($assets, $response->getBody()['assets']);
            }
        }

        return new AssetsData([
            'assets' => $assets
        ]);
    }
}
