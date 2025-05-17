<?php

namespace App\Enums;

enum StatusEnum: string
{
    case PENDING = 'pending';
    case PROGRESS = 'in progress';
    case COMPLETED = 'completed';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
