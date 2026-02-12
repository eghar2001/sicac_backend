<?php

namespace App\Http\Controllers;

use App\Models\TechnicianRequest;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TechnicianRequestController extends Controller
{
    /**
     * Obtener todas las solicitudes técnicas (solo admins) (READ)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.index: Intento de obtener todas las solicitudes', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            // Verificar que el usuario es un admin
            if ($user->role !== 'admin') {
                Log::warning('TechnicianRequest.index: ⚠️ Acceso denegado - Usuario no es admin', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso',
                ], 403);
            }

            // Obtener filtros opcionales
            $type = $request->query('type'); // technical_service, claim
            $status = $request->query('status'); // pending, assigned, completed, cancelled
            $technicianId = $request->query('technician_id');
            $search = $request->query('search');

            $query = TechnicianRequest::query()
                ->with('requestingUser', 'technician', 'category')
                ->orderBy('created_at', 'desc');

            // Aplicar filtros
            if ($type) {
                Log::debug('TechnicianRequest.index: Filtrando por type', ['type' => $type]);
                $query->where('type', $type);
            }

            if ($status) {
                Log::debug('TechnicianRequest.index: Filtrando por status', ['status' => $status]);
                $query->where('status', $status);
            }

            if ($technicianId) {
                Log::debug('TechnicianRequest.index: Filtrando por technician_id', ['technician_id' => $technicianId]);
                $query->where('technician_id', $technicianId);
            }

            if ($search) {
                Log::debug('TechnicianRequest.index: Buscando por texto', ['search' => $search]);
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }

            $requests = $query->get();

            Log::info('TechnicianRequest.index: ✅ Todas las solicitudes obtenidas exitosamente', [
                'user_id' => $userId,
                'requests_count' => $requests->count(),
                'filters' => [
                    'type' => $type,
                    'status' => $status,
                    'technician_id' => $technicianId,
                    'search' => $search,
                ],
            ]);

            return response()->json([
                'data' => $requests,
                'total' => $requests->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.index: ❌ Error al obtener todas las solicitudes', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de solicitudes (solo admins) (READ)
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.stats: Intento de obtener estadísticas', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            // Verificar que el usuario es un admin
            if ($user->role !== 'admin') {
                Log::warning('TechnicianRequest.stats: ⚠️ Acceso denegado - Usuario no es admin', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso',
                ], 403);
            }

            $stats = [
                'total' => TechnicianRequest::count(),
                'by_status' => [
                    'pending' => TechnicianRequest::where('status', 'pending')->count(),
                    'assigned' => TechnicianRequest::where('status', 'assigned')->count(),
                    'completed' => TechnicianRequest::where('status', 'completed')->count(),
                    'cancelled' => TechnicianRequest::where('status', 'cancelled')->count(),
                ],
                'by_type' => [
                    'technical_service' => TechnicianRequest::where('type', 'technical_service')->count(),
                    'claim' => TechnicianRequest::where('type', 'claim')->count(),
                ],
                'without_technician' => TechnicianRequest::whereNull('technician_id')->count(),
                'with_technician' => TechnicianRequest::whereNotNull('technician_id')->count(),
            ];

            Log::info('TechnicianRequest.stats: ✅ Estadísticas obtenidas exitosamente', [
                'user_id' => $userId,
                'stats' => $stats,
            ]);

            return response()->json([
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.stats: ❌ Error al obtener estadísticas', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear una nueva solicitud técnica o reclamo (CREATE)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();
        
        Log::info('TechnicianRequest.store: Intento de crear solicitud', [
            'user_id' => $userId,
        ]);

        try {
            $validatedData = $request->validate([
                'technician_id' => 'nullable|exists:technicians,id',
                'category_id' => 'nullable|exists:categories,id',
                'type' => 'required|in:technical_service,claim',
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'wanted_date_start' => 'required|date',
                'wanted_date_end' => 'required|date|after_or_equal:wanted_date_start',
                'time_shift' => 'required|string',
            ]);

            Log::debug('TechnicianRequest.store: Validación exitosa', [
                'user_id' => $userId,
                'type' => $validatedData['type'],
                'category_id' => $validatedData['category_id'] ?? null,
            ]);

            $technicianRequest = TechnicianRequest::create([
                'requesting_user_id' => $userId,
                'technician_id' => $validatedData['technician_id'] ?? null,
                'category_id' => $validatedData['category_id'] ?? null,
                'type' => $validatedData['type'],
                'status' => 'pending',
                'subject' => $validatedData['subject'],
                'description' => $validatedData['description'],
                'wanted_date_start' => $validatedData['wanted_date_start'],
                'wanted_date_end' => $validatedData['wanted_date_end'],
                'time_shift' => $validatedData['time_shift'],
            ]);

            Log::info('TechnicianRequest.store: ✅ Solicitud creada exitosamente', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'type' => $technicianRequest->type,
                'status' => $technicianRequest->status,
            ]);

            return response()->json([
                'message' => 'Solicitud creada correctamente',
                'data' => $technicianRequest->load('requestingUser', 'technician', 'category'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.store: ❌ Error al crear solicitud', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener las solicitudes creadas por el usuario autenticado (READ)
     */
    public function userRequests(Request $request)
    {
        $userId = Auth::id();

        Log::info('TechnicianRequest.userRequests: Intento de obtener solicitudes del usuario', [
            'user_id' => $userId,
        ]);

        try {
            $requests = TechnicianRequest::where('requesting_user_id', $userId)
                ->with('technician', 'category')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('TechnicianRequest.userRequests: ✅ Solicitudes obtenidas exitosamente', [
                'user_id' => $userId,
                'requests_count' => $requests->count(),
            ]);

            return response()->json([
                'data' => $requests,
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.userRequests: ❌ Error al obtener solicitudes', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener todas las solicitudes sin técnico asignado (disponibles) (READ)
     */
    public function unassignedRequests(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.unassignedRequests: Intento de obtener solicitudes sin asignar', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            // Verificar que el usuario es un technician
            if ($user->role !== 'technician') {
                Log::warning('TechnicianRequest.unassignedRequests: ⚠️ Acceso denegado - Usuario no es technician', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso',
                ], 403);
            }

            $requests = TechnicianRequest::whereNull('technician_id')
                ->where('status', '=', 'pending')
                ->orderBy('created_at', 'desc')
                ->with('requestingUser', 'category')
                ->get();

            Log::info('TechnicianRequest.unassignedRequests: ✅ Solicitudes disponibles obtenidas exitosamente', [
                'user_id' => $userId,
                'requests_count' => $requests->count(),
            ]);

            return response()->json([
                'data' => $requests,
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.unassignedRequests: ❌ Error al obtener solicitudes', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener las solicitudes asignadas al technician autenticado (READ)
     */
    public function myRequests(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.myRequests: Intento de obtener solicitudes', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            // Verificar que el usuario es un technician
            if ($user->role !== 'technician') {
                Log::warning('TechnicianRequest.myRequests: ⚠️ Acceso denegado - Usuario no es technician', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para acceder a este recurso',
                ], 403);
            }

            // Obtener el technician asociado al usuario autenticado
            $technician = Technician::where('user_id', $userId)->first();

            if (!$technician) {
                Log::warning('TechnicianRequest.myRequests: ⚠️ Technician no encontrado', [
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'message' => 'No eres un technician registrado',
                ], 404);
            }

            $requests = TechnicianRequest::where('technician_id', $technician->id)
                ->with('requestingUser', 'category')
                ->get();

            Log::info('TechnicianRequest.myRequests: ✅ Solicitudes obtenidas exitosamente', [
                'user_id' => $userId,
                'technician_id' => $technician->id,
                'requests_count' => $requests->count(),
            ]);

            return response()->json([
                'data' => $requests,
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.myRequests: ❌ Error al obtener solicitudes', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar el estado de una solicitud (UPDATE)
     */
    public function updateStatus(Request $request, TechnicianRequest $technicianRequest)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.updateStatus: Intento de actualizar estado', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'technician_request_id' => $technicianRequest->id,
            'current_status' => $technicianRequest->status,
        ]);

        try {
            // Verificar que el usuario es un technician o admin
            if ($user->role !== 'technician' && $user->role !== 'admin') {
                Log::warning('TechnicianRequest.updateStatus: ⚠️ Acceso denegado - Usuario no autorizado', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción',
                ], 403);
            }

            // Si es technician, verificar que la solicitud está asignada a él
            if ($user->role === 'technician') {
                $technician = Technician::where('user_id', $userId)->first();
                if (!$technician || $technicianRequest->technician_id !== $technician->id) {
                    Log::warning('TechnicianRequest.updateStatus: ⚠️ Solicitud no asignada a este technician', [
                        'user_id' => $userId,
                        'technician_id' => $technician?->id,
                        'request_technician_id' => $technicianRequest->technician_id,
                        'technician_request_id' => $technicianRequest->id,
                    ]);

                    return response()->json([
                        'message' => 'Esta solicitud no está asignada a ti',
                    ], 403);
                }
            }

            $validatedData = $request->validate([
                'status' => 'required|in:pending,assigned,completed,cancelled',
            ]);

            Log::debug('TechnicianRequest.updateStatus: Validación exitosa', [
                'user_id' => $userId,
                'new_status' => $validatedData['status'],
                'technician_request_id' => $technicianRequest->id,
            ]);

            $oldStatus = $technicianRequest->status;
            $technicianRequest->update($validatedData);

            Log::info('TechnicianRequest.updateStatus: ✅ Estado actualizado exitosamente', [
                'user_id' => $userId,
                'user_role' => $user->role,
                'technician_request_id' => $technicianRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $technicianRequest->status,
            ]);

            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'data' => $technicianRequest->load('requestingUser', 'technician', 'category'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.updateStatus: ❌ Error al actualizar estado', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar una solicitud técnica (admin puede modificar todo) (UPDATE)
     */
    public function update(Request $request, TechnicianRequest $technicianRequest)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.update: Intento de actualizar solicitud', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'technician_request_id' => $technicianRequest->id,
        ]);

        try {
            // Verificar que el usuario es un admin
            if ($user->role !== 'admin') {
                Log::warning('TechnicianRequest.update: ⚠️ Acceso denegado - Usuario no es admin', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción',
                ], 403);
            }

            // Validación condicional según el tipo
            $baseRules = [
                'type' => 'sometimes|in:technical_service,claim',
                'subject' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category_id' => 'nullable|exists:categories,id',
                'status' => 'sometimes|in:pending,assigned,completed,cancelled',
                'technician_id' => 'nullable|exists:technicians,id',
            ];

            $rules = $baseRules;
            if ($request->has('type') && $request->input('type') === 'technical_service') {
                $rules = array_merge($rules, [
                    'wanted_date_start' => 'nullable|date',
                    'wanted_date_end' => 'nullable|date|after_or_equal:wanted_date_start',
                    'time_shift' => 'nullable|string|in:morning,afternoon',
                ]);
            }

            $validatedData = $request->validate($rules);

            Log::debug('TechnicianRequest.update: Validación exitosa', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'updated_fields' => array_keys($validatedData),
            ]);

            $oldData = $technicianRequest->toArray();
            $technicianRequest->update($validatedData);

            Log::info('TechnicianRequest.update: ✅ Solicitud actualizada exitosamente', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'changes' => array_diff_assoc($technicianRequest->toArray(), $oldData),
            ]);

            return response()->json([
                'message' => 'Solicitud actualizada correctamente',
                'data' => $technicianRequest->load('requestingUser', 'technician', 'category'),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('TechnicianRequest.update: ⚠️ Error de validación', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.update: ❌ Error al actualizar solicitud', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Asignar una solicitud a sí mismo (solo para técnicos con solicitudes sin asignar)
     */
    public function assignToMyself(Request $request, TechnicianRequest $technicianRequest)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.assignToMyself: Intento de asignarse una solicitud', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'technician_request_id' => $technicianRequest->id,
        ]);

        try {
            // Verificar que el usuario es un technician
            if ($user->role !== 'technician') {
                Log::warning('TechnicianRequest.assignToMyself: ⚠️ Acceso denegado - Usuario no es technician', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción',
                ], 403);
            }

            // Obtener el technician asociado al usuario autenticado
            $technician = Technician::where('user_id', $userId)->first();

            if (!$technician) {
                Log::warning('TechnicianRequest.assignToMyself: ⚠️ Technician no encontrado', [
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'message' => 'No eres un technician registrado',
                ], 404);
            }

            // Verificar que la solicitud no tiene technician asignado
            if ($technicianRequest->technician_id !== null) {
                Log::warning('TechnicianRequest.assignToMyself: ⚠️ Solicitud ya tiene technician asignado', [
                    'user_id' => $userId,
                    'technician_id' => $technician->id,
                    'request_technician_id' => $technicianRequest->technician_id,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'Esta solicitud ya tiene un technician asignado',
                ], 403);
            }

            // Verificar que la solicitud está en estado pending
            if ($technicianRequest->status !== 'pending') {
                Log::warning('TechnicianRequest.assignToMyself: ⚠️ Solicitud no está en estado pending', [
                    'user_id' => $userId,
                    'technician_id' => $technician->id,
                    'current_status' => $technicianRequest->status,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'Solo puedes asignarte solicitudes en estado pendiente',
                ], 422);
            }

            // Asignar la solicitud al technician y cambiar estado a assigned
            $technicianRequest->update([
                'technician_id' => $technician->id,
                'status' => 'assigned',
            ]);

            Log::info('TechnicianRequest.assignToMyself: ✅ Solicitud asignada exitosamente', [
                'user_id' => $userId,
                'technician_id' => $technician->id,
                'technician_request_id' => $technicianRequest->id,
                'new_status' => 'assigned',
            ]);

            return response()->json([
                'message' => 'Te has asignado la solicitud correctamente',
                'data' => $technicianRequest->load('requestingUser', 'technician', 'category'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.assignToMyself: ❌ Error al asignarse la solicitud', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar una solicitud técnica (solo admins) (DELETE)
     */
    public function destroy(Request $request, TechnicianRequest $technicianRequest)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('TechnicianRequest.destroy: Intento de eliminar solicitud', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'technician_request_id' => $technicianRequest->id,
        ]);

        try {
            // Verificar que el usuario es un admin
            if ($user->role !== 'admin') {
                Log::warning('TechnicianRequest.destroy: ⚠️ Acceso denegado - Usuario no es admin', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'technician_request_id' => $technicianRequest->id,
                ]);

                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción',
                ], 403);
            }

            $requestId = $technicianRequest->id;
            $requestData = $technicianRequest->toArray();

            $technicianRequest->delete();

            Log::info('TechnicianRequest.destroy: ✅ Solicitud eliminada exitosamente', [
                'user_id' => $userId,
                'technician_request_id' => $requestId,
                'deleted_data' => $requestData,
            ]);

            return response()->json([
                'message' => 'Solicitud eliminada correctamente',
                'data' => $requestData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('TechnicianRequest.destroy: ❌ Error al eliminar solicitud', [
                'user_id' => $userId,
                'technician_request_id' => $technicianRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}