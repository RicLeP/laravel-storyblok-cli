<?php

use Riclep\StoryblokCli\Endpoints\Spaces;

test('example', function () {
    expect(true)->toBeTrue();
});

test('mock', function () {
    $spaces = Spaces::make('aaaa');
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
