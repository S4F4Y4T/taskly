<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Task\StoreTaskRequest;
use App\Http\Requests\V1\Task\UpdateTaskRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TaskResource::collection(
            auth()->user()->tasks()->latest()->paginate()
        );
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = auth()->user()->tasks()->create(
            $request->validated()
        );

        return self::success('Task created successfully', 201, TaskResource::make($task));
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);
        return TaskResource::make($task);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);
        $task = tap($task)->update($request->validated());
        return self::success('Task updated successfully', 200, TaskResource::make($task));
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return self::success('Task Deleted successfully');
    }
}
