<?php

use Riclep\StoryblokCli\Endpoints\ComponentGroups;
use Riclep\StoryblokCli\Endpoints\Components;

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
            'name' => 'Body blocks',
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
            'name' => 'Body blocks',
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
