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
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/technician/login', [AuthController::class, 'technicianLogin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(middleware: 'auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::post('/technician-requests', [TechnicianRequestController::class, 'startTechnicianRequest'])->middleware('auth:sanctum');


Route::resource('technician', TechnicianController::class);
Route::prefix('products/filters')->group(function () {
    Route::get('brands', [ProductFilterController::class, 'brands']);
    Route::get('categories', [ProductFilterController::class, 'categories']);
    Route::get('families', [ProductFilterController::class, 'families']);
    Route::get('subfamilies', [ProductFilterController::class, 'subfamilies']);
});
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('brands', BrandController::class)->only(['index']);
Route::apiResource('categories', CategoryController::class)->only(['index']);
Route::apiResource('families', FamilyController::class)->only(['index']);
Route::apiResource('subfamilies', SubfamilyController::class)->only(['index']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
});
