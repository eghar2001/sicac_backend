<?php

namespace App\Http\Controllers;

use App\Http\Traits\HasCacheHelpers;

abstract class Controller extends \Illuminate\Routing\Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests,
        \Illuminate\Foundation\Bus\DispatchesJobs,
        \Illuminate\Foundation\Validation\ValidatesRequests,
        HasCacheHelpers;

    public function __construct()
    {
    }
}
