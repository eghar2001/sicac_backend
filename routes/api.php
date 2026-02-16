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
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RatingSummaryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/admin', [AuthController::class, 'loginAdmin']);
Route::post('/login/technician', [AuthController::class, 'loginTechnician']);
Route::post('/login/user', [AuthController::class, 'loginUser']);
Route::post('/create-admin', [AuthController::class, 'createAdmin']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(middleware: 'auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->group(function () {
    // Usuarios: crear solicitudes técnicas
    Route::post('/technician-requests', [TechnicianRequestController::class, 'store']);
    
    // Usuarios: ver sus solicitudes creadas
    Route::get('/technician-requests/user-requests', [TechnicianRequestController::class, 'userRequests']);
    
    // Admins: obtener todas las solicitudes (con filtros opcionales)
    Route::get('/technician-requests/admin/all', [TechnicianRequestController::class, 'index']);
    
    // Admins: obtener estadísticas
    Route::get('/technician-requests/admin/stats', [TechnicianRequestController::class, 'stats']);
    
    // Technicians: ver solicitudes disponibles (sin técnico asignado)
    Route::get('/technician-requests/unassigned', [TechnicianRequestController::class, 'unassignedRequests']);
    
    // Technicians: asignarse a sí mismo una solicitud sin técnico
    Route::patch('/technician-requests/{technicianRequest}/assign-to-myself', [TechnicianRequestController::class, 'assignToMyself']);
    
    // Technicians: ver sus solicitudes asignadas
    Route::get('/technician-requests/my-requests', [TechnicianRequestController::class, 'myRequests']);
    
    // Technicians: actualizar estado de una solicitud
    Route::patch('/technician-requests/{technicianRequest}/status', [TechnicianRequestController::class, 'updateStatus']);
    
    // Admins: actualizar cualquier campo de una solicitud
    Route::patch('/technician-requests/{technicianRequest}', [TechnicianRequestController::class, 'update']);
    
    // Admins: eliminar una solicitud
    Route::delete('/technician-requests/{technicianRequest}', [TechnicianRequestController::class, 'destroy']);

    // Usuarios: crear reclamos
    Route::post('/claims', [ClaimController::class, 'store']);

    // Usuarios: ver sus reclamos
    Route::get('/claims/user-claims', [ClaimController::class, 'userClaims']);

    // Admins: obtener todos los reclamos
    Route::get('/claims/admin/all', [ClaimController::class, 'index']);

    // Admins: obtener estadísticas de reclamos
    Route::get('/claims/admin/stats', [ClaimController::class, 'stats']);

    // Admins: actualizar estado de un reclamo
    Route::patch('/claims/{claim}/status', [ClaimController::class, 'updateStatus']);

    // Admins: actualizar reclamo
    Route::patch('/claims/{claim}', [ClaimController::class, 'update']);

    // Admins: eliminar reclamo
    Route::delete('/claims/{claim}', [ClaimController::class, 'destroy']);
});

Route::apiResource('technicians', TechnicianController::class);

Route::post('/technicians/{technician}/reviews', [RatingController::class, 'store'])->middleware('auth:sanctum');

Route::get('/ratings', [RatingSummaryController::class, 'index']);

Route::prefix('products/filters')->group(function () {
    Route::get('/brands', [ProductFilterController::class, 'brands']);
    Route::get('/categories', [ProductFilterController::class, 'categories']);
    Route::get('/families', [ProductFilterController::class, 'families']);
    Route::get('/subfamilies', [ProductFilterController::class, 'subfamilies']);
});
