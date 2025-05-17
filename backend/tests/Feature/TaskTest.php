<?php

declare(strict_types=1);

use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\{postJson, putJson, deleteJson, assertDatabaseHas};

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

dataset('valid payload', [
    'default task' => [
        [
            'title' => 'Task 1',
            'description' => 'Test description.',
        ],
    ],
]);

dataset('invalid payload', [
    'missing title' => [
        [
            'title' => '',
            'description' => 'Some text',
        ],
    ]
]);

describe('Task CRUD Operations', function () {

    it('should fail to create a task with invalid input', function (array $payload): void {
        $response = postJson(route('v1.tasks.store'), $payload);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    })->with('invalid payload');

    it('should successfully create a task with valid data', function (array $payload): void
    {
        $response = postJson(route('v1.tasks.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'title', 'description']]);

        assertDatabaseHas('tasks', [
            'title' => $payload['title'],
            'user_id' => $this->user->id,
        ]);
    })->with('valid payload');

    it('should update an existing task', function (array $payload): void {
        $task = postJson(route('v1.tasks.store'), $payload)->json('data');
        $updatedPayload = [...$payload, 'title' => 'Updated Task Title'];

        $response = putJson(route('v1.tasks.update', $task['id']), $updatedPayload);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Task Title');

        assertDatabaseHas('tasks', [
            'id' => $task['id'],
            'title' => 'Updated Task Title',
        ]);
    })->with('valid payload');

    it('should delete a task', function (array $payload): void
    {
        $task = postJson(route('v1.tasks.store'), $payload)->json('data');
        $response = deleteJson(route('v1.tasks.destroy', $task['id']));
        $response->assertOk();

        expect(Task::find($task['id']))->toBeNull();
    })->with('valid payload');
});

test('user cannot update another user’s task', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $task = Task::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($otherUser);

    $response = putJson(route('v1.tasks.update', $task->id), [
        'title' => 'Hacked',
        'description' => 'Unauthorized update',
    ]);

    $response->assertForbidden(); // or ->assertStatus(403)
});

test('user cannot delete another user’s task', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $task = Task::factory()->create(['user_id' => $owner->id]);

    Sanctum::actingAs($otherUser);

    $response = deleteJson(route('v1.tasks.destroy', $task->id));
    $response->assertForbidden(); // or ->assertStatus(403)
});
