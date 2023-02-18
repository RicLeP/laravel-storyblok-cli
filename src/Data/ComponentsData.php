<?php

namespace Riclep\StoryblokCli\Data;

use Illuminate\Support\Collection;

class ComponentsData
{
    private array $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getComponents(): Collection
    {
        return collect($this->response['components']);
    }

    public function getComponentGroups(): Collection
    {
        return collect($this->response['component_groups']);
    }
}
