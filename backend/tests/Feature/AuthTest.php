<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can register', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'data']);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
});


test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'data' => ['token'],
        ]);
});

use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

test('unauthenticated users cannot access protected routes', function () {
    getJson('/api/v1/user')->assertUnauthorized();
    postJson(route('v1.tasks.store'))->assertUnauthorized();
    putJson(route('v1.tasks.update', 1))->assertUnauthorized();
    deleteJson(route('v1.tasks.destroy', 1))->assertUnauthorized();
});

