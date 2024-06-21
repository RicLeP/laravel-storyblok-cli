<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class AssetsData extends BasicData
{
	public function getAsset(): Collection {
		return collect($this->response);
	}

    public function getAssets(): Collection
    {
        return collect($this->response['assets']);
    }
}
