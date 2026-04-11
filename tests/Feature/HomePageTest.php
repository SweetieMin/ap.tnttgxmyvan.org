<?php

use App\Models\User;

test('guests can view the welcome page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSeeText('Hệ thống quản lý giáo lý')
        ->assertSeeText('Quản lý thiếu nhi và lớp học rõ ràng, đúng hạn, dễ theo dõi.');
});

test('authenticated users are redirected from home to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});
