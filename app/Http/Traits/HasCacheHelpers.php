<?php

namespace App\Http\Traits;

use DateTimeInterface;
use Illuminate\Http\Request;

trait HasCacheHelpers
{
    protected function cacheKey(Request $request, string $prefix): string
    {
        $query = $this->normalizeQueryParams($request->query());

        if ($query === []) {
            return $prefix;
        }

        return $prefix . ':' . md5(json_encode($query));
    }

    protected function cacheTtl(): DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    private function normalizeQueryParams(array $params): array
    {
        ksort($params);

        foreach ($params as $key => $value) {
            $params[$key] = $this->normalizeQueryValue($value);
        }

        return $params;
    }

    private function normalizeQueryValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = array_map(fn ($item) => $this->normalizeQueryValue($item), $value);

        if (array_is_list($normalized)) {
            usort($normalized, fn ($a, $b) => strcmp(json_encode($a), json_encode($b)));

            return $normalized;
        }

        ksort($normalized);

        return $normalized;
    }
}
