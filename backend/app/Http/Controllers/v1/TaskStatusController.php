<?php

namespace App\Http\Controllers\v1;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Task\UpdateTaskStatusRequest;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TaskStatusController extends Controller
{
    public function __invoke(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        Gate::authorize($task);

        if($task->status === StatusEnum::COMPLETED) {
            return self::error('Task already completed', 400);
        }

        $task = tap($task)->update($request->validated());
        return self::success('Status updated successfully', 200, TaskResource::make($task));
    }
}
