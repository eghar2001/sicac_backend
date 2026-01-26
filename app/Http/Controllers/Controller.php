<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function cacheKey(Request $request, string $prefix): string
    {
        return $prefix . ':' . md5($request->fullUrl());
    }

    protected function cacheTtl(): \DateTimeInterface
    {
        return now()->addMinutes(10);
    }
}
