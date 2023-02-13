<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Str;
use InvalidArgumentException;

class WritesComponentJson
{
	protected $json;

	public function __construct($json)
	{
		$this->json = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	}

	public function name($name)
	{
		$this->json['name'] = $name;
		$this->json['real_name'] = $name;
		return $this;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function group($group)
	{
		if (Str::isUuid($group)) {
			$this->json['component_group_uuid'] = $group;
			return $this;
		} else {
			throw new InvalidArgumentException ('Expected component group UUID');
		}
	}

	public function toJson()
	{
		return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
	}

	public function toArray()
	{
		return $this->json;
	}
}