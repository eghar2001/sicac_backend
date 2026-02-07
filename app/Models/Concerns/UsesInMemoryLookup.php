<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;

trait UsesInMemoryLookup
{
    protected static array $inMemoryCache = [];

    abstract protected static function dataSource(): array;
    abstract public static function primaryKey(): string;

    public static function collection(): Collection
    {
        $class = static::class;

        if (!isset(static::$inMemoryCache[$class])) {
            $items = static::dataSource();

            $models = collect($items)->map(function ($item, $key) use ($class) {
                return new $class(...$item);
            })->values();

            static::$inMemoryCache[$class] = $models;
        }

        return static::$inMemoryCache[$class];
    }

    public static function clearInMemoryCache(): void
    {
        unset(static::$inMemoryCache[static::class]);
    }
}
