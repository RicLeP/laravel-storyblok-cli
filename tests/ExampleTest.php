<?php

use Riclep\StoryblokCli\Endpoints\Spaces;
use Riclep\StoryblokCli\Endpoints\Stories;


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

test('can get a single story', function () {
	$stories = Stories::make('dummy-token');
	$stories->mockable([
		mockResponse('stories/259645264'),
	]);

	$storiesData = $stories->byId(259645264);

	expect($storiesData->getStory()['name'])->toEqual('Home');
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

