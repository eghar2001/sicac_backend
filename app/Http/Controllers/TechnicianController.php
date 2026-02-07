<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use Illuminate\Http\Request;

use App\Http\Resources\TechnicianResource;

class TechnicianController extends Controller
{
    public function index()
    {
        return TechnicianResource::collection(Technician::with('user')->get());
    }

    public function store(Request $request)
    {
        \Log::info('Technician store attempt', $request->all());

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'dni' => 'required|string|unique:users,dni',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'availability_date' => 'nullable|date',
            ]);
    
            $user = \App\Models\User::create([
                'name' => $validatedData['first_name'] . ' ' . $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validatedData['password']),
                'role' => 'technician',
                'dni' => $validatedData['dni'],
                'phone' => $validatedData['phone'],
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
            ]);
    
            $technician = Technician::create([
                'user_id' => $user->id,
                'availability_date' => $validatedData['availability_date'],
            ]);
    
            return new TechnicianResource($technician->load('user'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Technician validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Technician creation failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        return new TechnicianResource(Technician::with('user')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        \Log::info('Technician update attempt', ['id' => $id, 'data' => $request->all()]);

        try {
            $technician = Technician::with('user')->findOrFail($id);
            $user = $technician->user;
    
            $validatedData = $request->validate([
                'first_name' => 'string',
                'last_name' => 'string',
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:6',
                'dni' => 'string|unique:users,dni,' . $user->id,
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'availability_date' => 'nullable|date',
            ]);
    
            $userUpdates = $request->only(['email', 'dni', 'phone', 'address', 'city']);
            
            // Update name if first/last provided
            if ($request->has('first_name') || $request->has('last_name')) {
                $firstName = $request->input('first_name') ?? explode(' ', $user->name)[0];
                $lastName = $request->input('last_name') ?? explode(' ', $user->name)[1] ?? '';
                $userUpdates['name'] = trim("$firstName $lastName");
            }
    
            if ($request->filled('password')) {
                $userUpdates['password'] = \Illuminate\Support\Facades\Hash::make($request->input('password'));
            }
    
            $user->update($userUpdates);
    
            // Only update availability_date on Technician model
            if ($request->has('availability_date')) {
                $technician->update(['availability_date' => $request->input('availability_date')]);
            }
            
            return new TechnicianResource($technician->fresh(['user']));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Technician update validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Technician update failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        // Delete user, which should cascade or we delete explicitly
        if ($technician->user) {
            $technician->user->delete();
        } else {
            $technician->delete();
        }
        
        return response()->json(null, 204);
    }
}
