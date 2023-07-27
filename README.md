# laravel-auth-sdk
A simple library for using PTDTN OAuth Authentication.

## Installation
Use composer to manage your dependencies and download PHP-JWT:

`composer require ptdtn/laravel-auth-sdk`

## Configuration

Run `php artisan vendor:publish --tag="ptdtntoken"` to copy default configuration to your project

Run `php artisan migrate` to add column ptdtnuser_id to users table

Edit your `config/ptdtntoken.php` according to your PTDTN client ID

Edit your laravel guard configuration in `config/auth.php` to use the provided guard

``
'guards' => [
    'web' => [
        'driver' => 'ptdtntoken',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'ptdtntoken',
        'provider' => 'users',
    ]
],
``