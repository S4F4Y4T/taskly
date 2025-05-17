<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
