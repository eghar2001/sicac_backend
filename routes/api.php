<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(middleware: 'auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::apiResource('technicians', TechnicianController::class);
