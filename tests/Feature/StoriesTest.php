<?php

use Riclep\StoryblokCli\Endpoints\Stories;

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
            'body' => [],
        ],
        'publish' => 1,
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
            'body' => [],
        ],
        'publish' => 1,
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
