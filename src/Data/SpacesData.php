<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class SpacesData extends BasicData
{
    public function getSpaces(): Collection
    {
        return collect($this->response['spaces']);
    }
}
