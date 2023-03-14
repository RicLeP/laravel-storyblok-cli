<?php

namespace Riclep\StoryblokCli\Endpoints;

use Riclep\StoryblokCli\Data\StoriesData;

class Stories extends BasicEndpoint
{
    public function __construct($token)
    {
        parent::__construct($token);
    }

    public static function make($token = null): self
    {
        return new self(parent::getToken($token));
    }

    public function byId($storyId): StoriesData
    {
        return new StoriesData(
            $this->client->get('stories/' . $storyId)->getBody()
        );
    }

	public function all(): StoriesData
	{
		return new StoriesData(
			$this->client->get('stories')->getBody()
		);
	}

	public function search($search): StoriesData
	{
		return new StoriesData(
			$this->client->get('stories/?text_search=' . urlencode($search))->getBody()
		);
	}

	public function create($story): StoriesData {
		if (is_array($story)) {
			$story = json_encode($story);
		}

		return new StoriesData(
			$this->client->post('stories', $story)->getBody()
		);
	}

	public function update($id, $story): StoriesData {
		if (is_array($story)) {
			$story = json_encode($story);
		}

		return new StoriesData(
			$this->client->put('stories/' . $id, $story)->getBody()
		);
	}

	public function delete($id) {
		return new StoriesData(
			$this->client->delete('stories/' . $id)->getBody()
		);
	}

	public function publish($id): StoriesData {
		return new StoriesData(
			$this->client->get('stories/' . $id . '/publish')->getBody()
		);
	}

	public function unpublish($id): StoriesData {
		return new StoriesData(
			$this->client->get('stories/' . $id . '/unpublish')->getBody()
		);
	}
}
