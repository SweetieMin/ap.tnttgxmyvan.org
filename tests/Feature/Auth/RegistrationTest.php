<?php

use Illuminate\Support\Facades\Route;

test('registration routes are disabled', function () {
    expect(Route::has('register'))->toBeFalse();
    expect(Route::has('register.store'))->toBeFalse();
});

test('registration endpoints are not accessible', function () {
    $response = $this->get('/register');

    $response->assertNotFound();

    $response = $this->post('/register', [
        'name' => 'John Doe',
        'username' => 'john.doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
});
