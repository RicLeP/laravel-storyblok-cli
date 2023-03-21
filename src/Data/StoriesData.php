<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class StoriesData extends BasicData
{
    public function getStory(): Collection
    {
        return collect($this->response['story']);
    }

    public function getStories(): Collection
    {
        return collect($this->response['stories']);
    }
}
