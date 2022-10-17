<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Storyblok Personal access token
	|--------------------------------------------------------------------------
	|
	| Enter your Storyblok Personal access token to access their management API
	|
	*/
	'oauth_token' => env('STORYBLOK_OAUTH_TOKEN', null),

	/*
	|--------------------------------------------------------------------------
	| Storyblok Space ID
	|--------------------------------------------------------------------------
	|
	| Enter your Storyblok space ID for use with the management API
	|
	*/
	'space_id' => env('STORYBLOK_SPACE_ID', null),
];
