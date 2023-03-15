<?php

use Riclep\StoryblokCli\Endpoints\ComponentGroups;
use Riclep\StoryblokCli\Endpoints\Components;
use Riclep\StoryblokCli\Endpoints\Spaces;
use Riclep\StoryblokCli\Endpoints\Stories;




test('returns empty collection when data does not exist', function () {
	$spaces = Spaces::make('dummy-token');
	$spaces->mockable([
		mockResponse('spaces/196985'),
	]);

	$spaceData = $spaces->byId(196985);

	dd($spaceData->dump());

	$spaceData = $spaces->byId(196985)->getCollectionFromResponse('not-here');

	expect($spaceData)->not()->toBeNull();
	expect($spaceData)->toHaveCount(0);
	expect($spaceData)->toBeCollection();
});


test('can get spaces', function () {
    $spaces = Spaces::make('dummy-token');
    $spaces->mockable([
        mockResponse('spaces'),
    ]);

    $spacesData = $spaces->all();
    expect($spacesData->getSpaces())->not()->toBeNull();
    expect($spacesData->getSpaces())->toHaveCount(2);

    $spaceValues = $spacesData->getSpaces();
    expect($spaceValues[0]['name'])->toEqual('Example Space 1');
    expect($spaceValues[1]['name'])->toEqual('Example Space 2');
});


test('can get a space by id', function () {
    $spaces = Spaces::make('dummy-token');
    $spaces->mockable([
        mockResponse('spaces/196985'),
    ]);

    $spaceData = $spaces->byId(196985);

    $spaceValue = $spaceData->getSpace();
    expect($spaceValue['name'])->toEqual('CLI space');
});


test('can set space id', function () {
	$story = Stories::make('dummy-token');
	$story->mockable([
		mockResponse('stories/259645264'),
	]);

	$storyData = $story->spaceId(12345)->byId(259645264);

	expect($storyData->getStory()['name'])->toEqual('Home');
});


test('can get a single story', function () {
	$story = Stories::make('dummy-token');
	$story->mockable([
		mockResponse('stories/259645264'),
	]);

	$storyData = $story->byId(259645264);

	expect($storyData->getStory()['name'])->toEqual('Home');
});

test('can get stories', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories'),
	]);

	$storiesData = $stories->all();

	expect($storiesData->getStories())->not()->toBeNull();
	expect($storiesData->getStories())->toHaveCount(3);

	expect($storiesData->getStories()[0]['name'])->toEqual('Test story');
});

test('can search stories', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/search-results'),
	]);

	$storiesData = $stories->search('test');

	expect($storiesData->getStories())->not()->toBeNull();
	expect($storiesData->getStories())->toHaveCount(1);

	expect($storiesData->getStories()[0]['name'])->toEqual('Test story');
});

test('can create a story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->create([
		'name' => 'Home',
		'slug' => 'home',
		'content' => [
			'component' => 'page',
			'body' => []
		],
		'publish' => 1
	]);

	expect($storiesData->getStory()['name'])->toEqual('Home');
});

test('can update a story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->update(259645264, [
		'name' => 'Home',
		'slug' => 'home',
		'content' => [
			'component' => 'page',
			'body' => []
		],
		'publish' => 1
	]);

	expect($storiesData->getStory()['name'])->toEqual('Home');
});

test('can delete a story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->delete(259645264);

	expect($storiesData->getStory()['name'])->toEqual('Home');
});

test('can publish a story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->publish(259645264);

	expect($storiesData->getStory()['name'])->toEqual('Home');
});

test('can unpublish a story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->unpublish(259645264);

	expect($storiesData->getStory()['name'])->toEqual('Home');
});



test('can get components for space', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components'),
	]);

	$componentsData = $components->all();

	expect($componentsData->getComponents())->not()->toBeNull();
	expect($componentsData->getComponents())->toHaveCount(5);
	expect($componentsData->getComponents()[0]['name'])->toEqual('page');
});

test('can get component by ID', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components/2559045'),
	]);

	$componentData = $components->byId(2559045);

	expect($componentData->getComponent()['name'])->toEqual('hero');
});

test('can create a component', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components/2559045'),
	]);

	$componentData = $components->create([
		'component' => [
			'name' => 'hero',
			'display_name' => 'Hero',
			'schema' => [
				'title' => [
					'type' => 'text',
					'pos' => 0,
				],
				'image' => [
					'type' => 'image',
					'pos' => 1,
				],
			],
			'is_root' => false,
			'is_nestable' => true,
		],
	]);

	expect($componentData->getComponent()['name'])->toEqual('hero');
});

test('can update a component', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components/2559045'),
	]);

	$componentData = $components->update(2559045, [
		'component' => [
			'name' => 'hero',
			'display_name' => 'Hero',
			'schema' => [
				'title' => [
					'type' => 'text',
					'pos' => 0,
				],
				'image' => [
					'type' => 'image',
					'pos' => 1,
				],
			],
			'is_root' => false,
			'is_nestable' => true,
		],
	]);

	expect($componentData->getComponent()['name'])->toEqual('hero');
});

test('can delete a component', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components/2559045'),
	]);

	$componentData = $components->delete(2559045);

	expect($componentData->getComponent()['name'])->toEqual('hero');
});




test('can get component groups', function () {
	$components = Components::make('dummy-token');
	$components->mockable([
		mockResponse('components'),
	]);

	$componentsData = $components->all();

	expect($componentsData->getComponentGroups())->not()->toBeNull();
	expect($componentsData->getComponentGroups())->toHaveCount(2);
	expect($componentsData->getComponentGroups()[0]['name'])->toEqual('Body blocks');
});

test('can get all component groups for space', function () {
	$componentGroups = ComponentGroups::make('dummy-token');
	$componentGroups->mockable([
		mockResponse('component_groups'),
	]);

	$componentGroupsData = $componentGroups->all();

	expect($componentGroupsData->getComponentGroups())->not()->toBeNull();
	expect($componentGroupsData->getComponentGroups())->toHaveCount(2);
	expect($componentGroupsData->getComponentGroups()[0]['name'])->toEqual('Body blocks');
});

test('can get component group by ID', function () {
	$componentGroups = ComponentGroups::make('dummy-token');
	$componentGroups->mockable([
		mockResponse('component_groups/58878'),
	]);

	$componentGroupsData = $componentGroups->byId(58878);

	expect($componentGroupsData->getComponentGroup()['name'])->toEqual('Body blocks');
});

test('can create a component group', function () {
	$componentGroups = ComponentGroups::make('dummy-token');
	$componentGroups->mockable([
		mockResponse('component_groups/58878'),
	]);

	$componentGroupData = $componentGroups->create([
		'component_group' => [
			'name' => 'Body blocks'
		],
	]);

	expect($componentGroupData->getComponentGroup()['name'])->toEqual('Body blocks');
});

test('can update a component group', function () {
	$componentGroups = ComponentGroups::make('dummy-token');
	$componentGroups->mockable([
		mockResponse('component_groups/58878'),
	]);

	$componentGroupData = $componentGroups->update(58878, [
		'component_group' => [
			'name' => 'Body blocks'
		],
	]);

	expect($componentGroupData->getComponentGroup()['name'])->toEqual('Body blocks');
});

test('can delete a component group', function () {
	$componentGroups = ComponentGroups::make('dummy-token');
	$componentGroups->mockable([
		mockResponse('component_groups/58878'),
	]);

	$componentGroupData = $componentGroups->delete(58878);

	expect($componentGroupData->getComponentGroup()['name'])->toEqual('Body blocks');
});