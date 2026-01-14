<?php
// Generates a fluent URI instance for the given URI
uri('https://example.com')->withPath('/users')->withQuery(['page' => 1]);
uri([UserController::class, 'show'], ['user' => $user]); // create a Uri instance for the controller method's route path
uri(UserIndexController::class); // for invokable controller

// Generates a fully qualified URL to the given path
url('user/profile');
url()->current();
url()->full();
url()->previous();

// Generates a fully qualified HTTPS URL to the given path. 
secure_url('user/profile');
secure_url('user/profile', [1]);

// Generates a URL for the given controller action
action([TransactionTestController::class, 'index']);
action([UserController::class, 'profile'], ['id' => 1]); // with route parameter

// Generates url for assets:
asset('img/photo.jpg');
// We can take ASSET_URL env if we use any cdn
// In env: ASSET_URL=http://example.com/assets
asset('img/photo.jpg'); // http://example.com/assets/img/photo.jpg

// Generates a URL for an asset using HTTPS
secure_asset('img/photo.jpg');

// Generates a redirect HTTP response for a given controller action
to_action([UserController::class, 'show'], ['user' => 1]);
 to_action(
    [UserController::class, 'show'],
    ['user' => 1],
    302, // http status code
    ['X-Framework' => 'Laravel']
)

// Generates  redirect HTTP response for a given named route
to_route('users.show', ['user' => 1]); // also can pass status code and X- ...

// Generates a URL for a given named route:
route('route.name'); // absolute url
route('route.name', ['id' => 1]);
route('route.name', ['id' => 1], false); // relative url