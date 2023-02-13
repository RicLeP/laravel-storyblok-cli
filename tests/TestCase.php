<?php

namespace Riclep\StoryblokCli\Tests;

use InvalidArgumentException;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use Riclep\StoryblokCli\StoryblokCliServiceProvider;
use Riclep\StoryblokCli\WritesComponentJson;


class TestCase extends Orchestra
{
	protected function getEnvironmentSetUp($app)
	{
		$app->useStoragePath(__DIR__);

		// configure filesystems in test environment
		$app['config']->set('filesystems.disks.local', [
			'driver' => 'local',
			'root' => __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures',
		]);
	}



	protected function getPackageProviders($app)
	{
		return [StoryblokCliServiceProvider::class];
	}

	/** @test */
	public function can_set_json_name()
	{
		$writesComponentJson = new WritesComponentJson(json_encode([
			'name' => 'feature',
			'display_name' => null,
			'created_at' => '2023-02-09T16:00:22.302Z',
			'updated_at' => '2023-02-09T16:00:22.302Z',
			'id' => 3419531,
			'schema' => [
				'name' => [
					'type' => 'textarea',
				],
				'surname' => [
					'type' => 'text',
				],
			],
			'image' => null,
			'preview_field' => null,
			'is_root' => false,
			'preview_tmpl' => null,
			'is_nestable' => true,
			'all_presets' => [
			],
			'preset_id' => null,
			'real_name' => 'feature',
			'component_group_uuid' => null,
			'color' => null,
			'icon' => null,
		], JSON_THROW_ON_ERROR));

		$writesComponentJson->name('test');

		$this->assertEquals([
			'name' => 'test',
			'display_name' => null,
			'created_at' => '2023-02-09T16:00:22.302Z',
			'updated_at' => '2023-02-09T16:00:22.302Z',
			'id' => 3419531,
			'schema' => [
				'name' => [
					'type' => 'textarea',
				],
				'surname' => [
					'type' => 'text',
				],
			],
			'image' => null,
			'preview_field' => null,
			'is_root' => false,
			'preview_tmpl' => null,
			'is_nestable' => true,
			'all_presets' => [
			],
			'preset_id' => null,
			'real_name' => 'test',
			'component_group_uuid' => null,
			'color' => null,
			'icon' => null,
		], $writesComponentJson->toArray());

		$this->assertEquals('{"name":"test","display_name":null,"created_at":"2023-02-09T16:00:22.302Z","updated_at":"2023-02-09T16:00:22.302Z","id":3419531,"schema":{"name":{"type":"textarea"},"surname":{"type":"text"}},"image":null,"preview_field":null,"is_root":false,"preview_tmpl":null,"is_nestable":true,"all_presets":[],"preset_id":null,"real_name":"test","component_group_uuid":null,"color":null,"icon":null}', $writesComponentJson->toJson());
	}

	/** @test
	 * @throws \JsonException
	 */
	public function can_set_component_group()
	{
		$writesComponentJson = new WritesComponentJson(json_encode([
			'name' => 'feature',
			'display_name' => null,
			'created_at' => '2023-02-09T16:00:22.302Z',
			'updated_at' => '2023-02-09T16:00:22.302Z',
			'id' => 3419531,
			'schema' => [
				'name' => [
					'type' => 'textarea',
				],
				'surname' => [
					'type' => 'text',
				],
			],
			'image' => null,
			'preview_field' => null,
			'is_root' => false,
			'preview_tmpl' => null,
			'is_nestable' => true,
			'all_presets' => [
			],
			'preset_id' => null,
			'real_name' => 'feature',
			'component_group_uuid' => null,
			'color' => null,
			'icon' => null,
		], JSON_THROW_ON_ERROR));

		$writesComponentJson->group('1edfc6f7-0000-4193-8e25-203b897c066e');

		$this->assertEquals([
			'name' => 'feature',
			'display_name' => null,
			'created_at' => '2023-02-09T16:00:22.302Z',
			'updated_at' => '2023-02-09T16:00:22.302Z',
			'id' => 3419531,
			'schema' => [
				'name' => [
					'type' => 'textarea',
				],
				'surname' => [
					'type' => 'text',
				],
			],
			'image' => null,
			'preview_field' => null,
			'is_root' => false,
			'preview_tmpl' => null,
			'is_nestable' => true,
			'all_presets' => [
			],
			'preset_id' => null,
			'real_name' => 'feature',
			'component_group_uuid' => '1edfc6f7-0000-4193-8e25-203b897c066e',
			'color' => null,
			'icon' => null,
		], $writesComponentJson->toArray());

		$this->assertEquals('{"name":"feature","display_name":null,"created_at":"2023-02-09T16:00:22.302Z","updated_at":"2023-02-09T16:00:22.302Z","id":3419531,"schema":{"name":{"type":"textarea"},"surname":{"type":"text"}},"image":null,"preview_field":null,"is_root":false,"preview_tmpl":null,"is_nestable":true,"all_presets":[],"preset_id":null,"real_name":"feature","component_group_uuid":"1edfc6f7-0000-4193-8e25-203b897c066e","color":null,"icon":null}', $writesComponentJson->toJson());


		$this->expectException(InvalidArgumentException::class);
		$writesComponentJson->group('exception');
	}


	/** @test */
	public function xxcan_write_json_for_importing_components()
	{
		// stub method and override it’s return
//
//
//
//
//		// mock GetsComponents trait
//		$mock = $this->getMockBuilder('Riclep\StoryblokCli\Console\ImportComponentCommand')
//			->onlyMethods(['requestComponents'])->getMock();
//		$mock->method('requestComponents')->willReturn('fffffffdsfsdfsfsf');
//
//
//		$this->callMethod($mock, 'importComponent', ['component.json']);
	}

	/** @test */
	public function can_export_component()
	{
		// $this->artisan('ls:export-component test')->assertExitCode(1);
	}

	public static function callMethod($obj, $name, array $args = []) {
		$class = new ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}
}