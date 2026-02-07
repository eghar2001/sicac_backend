<?php

namespace App\Models;

use App\Models\Concerns\UsesInMemoryLookup;

class TimeShift
{
    use UsesInMemoryLookup;

    public function __construct(
        public string $name,
        public string $hour_start,
        public string $hour_end,
    ) {
    }

    protected static function dataSource(): array
    {
        return require database_path('data/time_shifts.php');
    }

    public static function primaryKey(): string
    {
        return 'name';
    }
}
