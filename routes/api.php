<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ProductFilterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubfamilyController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\TechnicianRequestController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RatingSummaryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(middleware: 'auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/technician-requests', [TechnicianRequestController::class, 'startTechnicianRequest'])->middleware('auth:sanctum');

Route::apiResource('technicians', TechnicianController::class);

Route::post('/technicians/{technician}/reviews', [RatingController::class, 'store'])->middleware('auth:sanctum');

Route::get('/ratings', [RatingSummaryController::class, 'index']);
