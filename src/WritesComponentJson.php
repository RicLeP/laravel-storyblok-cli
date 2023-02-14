<?php

namespace Riclep\StoryblokCli;

use Illuminate\Support\Str;
use InvalidArgumentException;

// TODO - add ability to write json file

class WritesComponentJson
{
	protected $json;

	public function __construct($json)
	{
		$this->json = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

		$this->cleanJson();
	}

	public function cleanJson() {
		unset($this->json['created_at'], $this->json['updated_at']);
	}

	public function name($name)
	{
		$this->json['name'] = $name;
		$this->json['real_name'] = $name;
		return $this;
	}

	public function getName() {
		return $this->json['name'];
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function group($group)
	{
		if (is_null($group) || Str::isUuid($group)) {
			$this->json['component_group_uuid'] = $group;
			return $this;
		} else {
			throw new InvalidArgumentException ('Expected component group UUID or null, got ' . $group . ' instead.');
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