<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\AssetFoldersData;

class AssetFolders extends BasicEndpoint
{
    public function __construct($token)
    {
	    parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

	public function byId($assetFolderId): AssetFoldersData
	{
		return new AssetFoldersData(
			$this->client->get('spaces/'.$this->spaceId.'/asset_folders/' . $assetFolderId)->getBody()
		);
	}

    public function all(): AssetFoldersData
    {
        return new AssetFoldersData(
            $this->client->get('spaces/'.$this->spaceId.'/asset_folders/')->getBody()
        );
    }

	public function create($assetFolder): AssetFoldersData {
		return new AssetFoldersData(
			$this->client->post('spaces/'.$this->spaceId.'/asset_folders/', $assetFolder)->getBody()
		);
	}

	public function update($id, $assetFolder): AssetFoldersData {
		return new AssetFoldersData(
			$this->client->put('spaces/'.$this->spaceId.'/asset_folders/' . $id, $assetFolder)->getBody()
		);
	}

	public function delete($id) {
		return new AssetFoldersData(
			$this->client->delete('spaces/'.$this->spaceId.'/asset_folders/' . $id)->getBody()
		);
	}
}
