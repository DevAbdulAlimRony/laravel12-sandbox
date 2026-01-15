<?php
// Returns an authenticator instance, alternative of Auth facade.
$admin = auth('admin')->user();
$user = auth()->user();

// Get or Set session:
session('key');
session(['chairs' => 7, 'instruments' => 3]);
session()->get('key'); // will be returned if no value is passed to the function
session()->put('key', $value);

// Data Hashing using bcrypt algorithm:
$password = bcrypt('my-secret-password');
$password = decrypt($value);

// Encrypt the given value:
$secret = encrypt('my-secret-value');

// Generate an html hidden input:
// {{ csrf_field() }}

// Generates an HTML hidden input field defining HTTP verb:
// {{ method_field('DELETE') }}

// Retrieves the value of the current CSRF token
$token = csrf_token();

// Retrive old input value flashed into the session:
$value = old('value', 'default');
// {{ old('name', $user->name) }}