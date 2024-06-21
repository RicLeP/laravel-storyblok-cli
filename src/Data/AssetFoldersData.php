<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class AssetFoldersData extends BasicData
{
	public function getAssetFolder(): Collection {
		return collect($this->response['asset_folder']);
	}

	public function getAssetFolders(): Collection
	{
		return collect($this->response['asset_folders']);
	}
}
