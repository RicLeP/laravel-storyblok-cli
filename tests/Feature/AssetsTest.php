<?php

use Riclep\StoryblokCli\Endpoints\AssetFolders;
use Riclep\StoryblokCli\Endpoints\Assets;

test('can get assets for space', function () {
    $assets = Assets::make('dummy-token');
    $assets->mockable([
        mockResponse('assets'),
    ]);

    $assetsData = $assets->all();

    expect($assetsData->getAssets())->not()->toBeNull();
    expect($assetsData->getAssets())->toHaveCount(4);
    expect($assetsData->getAssets()[0]['filename'])->toEqual('https://s3.amazonaws.com/a.storyblok.com/f/74398/397x262/ed7094c58f/theme-2.svg');
});



test('can get asset by ID', function () {
    $assets = Assets::make('dummy-token');
    $assets->mockable([
        mockResponse('assets/16294561'),
    ]);

    $assetData = $assets->byId(16294561);

    expect($assetData->getAsset()['filename'])->toEqual('https://s3.amazonaws.com/a.storyblok.com/f/74398/132x163/fa8ac7957f/gui-percentages.svg');
});

test('can get all asset folders for space', function () {
    $assetFolders = AssetFolders::make('dummy-token');
    $assetFolders->mockable([
        mockResponse('asset_folders'),
    ]);

    $assetFoldersData = $assetFolders->all();

    expect($assetFoldersData->getAssetFolders())->not()->toBeNull();
    expect($assetFoldersData->getAssetFolders())->toHaveCount(1);
    expect($assetFoldersData->getAssetFolders()[0]['name'])->toEqual('test-folder');
});


test('can get asset folder by ID', function () {
    $assetFolders = AssetFolders::make('dummy-token');
    $assetFolders->mockable([
        mockResponse('asset_folders/577341'),
    ]);

    $assetFoldersData = $assetFolders->byId(577341);

    expect($assetFoldersData->getAssetFolder()['name'])->toEqual('test-folder');
});


test('can create a component group', function () {
    $assetFolders = AssetFolders::make('dummy-token');
    $assetFolders->mockable([
        mockResponse('asset_folders/577341'),
    ]);

    $assetFoldersData = $assetFolders->create([
        'asset_folder' => [
            'name' => 'test-folder',
            'parent_id' =>  0
        ],
    ]);

    expect($assetFoldersData->getAssetFolder()['name'])->toEqual('test-folder');
});



test('can update a component group', function () {
    $assetFolders = AssetFolders::make('dummy-token');
    $assetFolders->mockable([
        mockResponse('asset_folders/577341'),
    ]);

    $assetFoldersData = $assetFolders->update(58878, [
        'component_group' => [
            'name' => 'test-folder',
            'parent_id' =>  0
        ],
    ]);

    expect($assetFoldersData->getAssetFolder()['name'])->toEqual('test-folder');
});


test('can delete a component group', function () {
    $assetFolders = AssetFolders::make('dummy-token');
    $assetFolders->mockable([
        mockResponse('asset_folders/577341'),
    ]);

    $assetFoldersData = $assetFolders->delete(58878);

    expect($assetFoldersData->getAssetFolder()['name'])->toEqual('test-folder');
});