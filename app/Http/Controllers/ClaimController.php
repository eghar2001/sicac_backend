<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClaimController extends Controller
{
    /**
     * Obtener todos los reclamos (solo admins) (READ)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('Claim.index: Intento de obtener todos los reclamos', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            $this->authorize('viewAny', Claim::class);

            $status = $request->query('status'); // pending, completed, cancelled
            $search = $request->query('search');

            $query = Claim::query()
                ->with('requestingUser', 'category')
                ->orderBy('created_at', 'desc');

            if ($status) {
                Log::debug('Claim.index: Filtrando por status', ['status' => $status]);
                $query->where('status', $status);
            }

            if ($search) {
                Log::debug('Claim.index: Buscando por texto', ['search' => $search]);
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $claims = $query->get();

            Log::info('Claim.index: ✅ Reclamos obtenidos exitosamente', [
                'user_id' => $userId,
                'claims_count' => $claims->count(),
            ]);

            return response()->json([
                'data' => $claims,
                'total' => $claims->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Claim.index: ❌ Error al obtener reclamos', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener estadísticas de reclamos (solo admins) (READ)
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('Claim.stats: Intento de obtener estadísticas', [
            'user_id' => $userId,
            'user_role' => $user->role,
        ]);

        try {
            $this->authorize('viewAny', Claim::class);

            $stats = [
                'total' => Claim::count(),
                'by_status' => [
                    Claim::STATUS_PENDING => Claim::where('status', Claim::STATUS_PENDING)->count(),
                    Claim::STATUS_COMPLETED => Claim::where('status', Claim::STATUS_COMPLETED)->count(),
                    Claim::STATUS_CANCELLED => Claim::where('status', Claim::STATUS_CANCELLED)->count(),
                ],
            ];

            Log::info('Claim.stats: ✅ Estadísticas obtenidas exitosamente', [
                'user_id' => $userId,
                'stats' => $stats,
            ]);

            return response()->json([
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Claim.stats: ❌ Error al obtener estadísticas', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Crear un nuevo reclamo (CREATE)
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        Log::info('Claim.store: Intento de crear reclamo', [
            'user_id' => $userId,
        ]);

        try {
            $this->authorize('create', Claim::class);

            $validatedData = $request->validate(Claim::storeRules());

            $claim = Claim::create([
                'requesting_user_id' => $userId,
                'category_id' => $validatedData['category_id'],
                'status' => Claim::STATUS_PENDING,
                'subject' => $validatedData['subject'],
                'description' => $validatedData['description'],
            ]);

            Log::info('Claim.store: ✅ Reclamo creado exitosamente', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'status' => $claim->status,
            ]);

            return response()->json([
                'message' => 'Reclamo creado correctamente',
                'data' => $claim->load('requestingUser', 'category'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Claim.store: ❌ Error al crear reclamo', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtener los reclamos creados por el usuario autenticado (READ)
     */
    public function userClaims(Request $request)
    {
        $userId = Auth::id();

        Log::info('Claim.userClaims: Intento de obtener reclamos del usuario', [
            'user_id' => $userId,
        ]);

        try {
            $this->authorize('viewOwn', Claim::class);

            $claims = Claim::where('requesting_user_id', $userId)
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Claim.userClaims: ✅ Reclamos obtenidos exitosamente', [
                'user_id' => $userId,
                'claims_count' => $claims->count(),
            ]);

            return response()->json([
                'data' => $claims,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Claim.userClaims: ❌ Error al obtener reclamos', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar estado de reclamo (solo admin) (UPDATE)
     */
    public function updateStatus(Request $request, Claim $claim)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('Claim.updateStatus: Intento de actualizar estado', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'claim_id' => $claim->id,
            'current_status' => $claim->status,
        ]);

        try {
            $this->authorize('updateStatus', $claim);

            $validatedData = $request->validate(Claim::statusUpdateRules());

            $oldStatus = $claim->status;
            $claim->update($validatedData);

            Log::info('Claim.updateStatus: ✅ Estado actualizado exitosamente', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'old_status' => $oldStatus,
                'new_status' => $claim->status,
            ]);

            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'data' => $claim->load('requestingUser', 'category'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Claim.updateStatus: ❌ Error al actualizar estado', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Actualizar reclamo (solo admin) (UPDATE)
     */
    public function update(Request $request, Claim $claim)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('Claim.update: Intento de actualizar reclamo', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'claim_id' => $claim->id,
        ]);

        try {
            $this->authorize('update', $claim);

            $validatedData = $request->validate(Claim::updateRules());

            $oldData = $claim->toArray();
            $claim->update($validatedData);

            Log::info('Claim.update: ✅ Reclamo actualizado exitosamente', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'changes' => array_diff_assoc($claim->toArray(), $oldData),
            ]);

            return response()->json([
                'message' => 'Reclamo actualizado correctamente',
                'data' => $claim->load('requestingUser', 'category'),
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Claim.update: ⚠️ Error de validación', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Claim.update: ❌ Error al actualizar reclamo', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Eliminar reclamo (solo admin) (DELETE)
     */
    public function destroy(Request $request, Claim $claim)
    {
        $user = Auth::user();
        $userId = $user->id;

        Log::info('Claim.destroy: Intento de eliminar reclamo', [
            'user_id' => $userId,
            'user_role' => $user->role,
            'claim_id' => $claim->id,
        ]);

        try {
            $this->authorize('delete', $claim);

            $claimId = $claim->id;
            $claimData = $claim->toArray();

            $claim->delete();

            Log::info('Claim.destroy: ✅ Reclamo eliminado exitosamente', [
                'user_id' => $userId,
                'claim_id' => $claimId,
            ]);

            return response()->json([
                'message' => 'Reclamo eliminado correctamente',
                'data' => $claimData,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Claim.destroy: ❌ Error al eliminar reclamo', [
                'user_id' => $userId,
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
