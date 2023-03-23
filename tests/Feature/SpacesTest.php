<?php

use Riclep\StoryblokCli\Endpoints\Spaces;
use Riclep\StoryblokCli\Endpoints\Stories;

test('returns empty collection when data does not exist', function () {
    $spaces = Spaces::make('dummy-token');
    $spaces->mockable([
        mockResponse('spaces/196985'),
    ]);

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
