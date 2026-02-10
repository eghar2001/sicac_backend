<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller extends \Illuminate\Routing\Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests,
        \Illuminate\Foundation\Bus\DispatchesJobs,
        \Illuminate\Foundation\Validation\ValidatesRequests;

    public function __construct()
    {
    }
}
