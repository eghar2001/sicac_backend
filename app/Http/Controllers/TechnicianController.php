<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    //GETALL
    public function index()
    {
        $technicians = Technician::with('user')->get();
        return response()->json($technicians);
    }

    //GUARDAR
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'dni' => 'required|unique:technicians,dni',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'availability_date' => 'nullable|date',
        ]);

        $technician = Technician::create($validatedData);

        return response()->json($technician, 201);
    }

    //GET BY ID
    public function show(string $id)
    {
        $technician = Technician::with('user')->findOrFail($id);
        return response()->json($technician);
    }

    //UPDATE
    public function update(Request $request, string $id)
    {
        $technician = Technician::findOrFail($id);

        $validatedData = $request->validate([
            'user_id' => 'exists:users,id',
            'dni' => 'unique:technicians,dni,' . $id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'availability_date' => 'nullable|date',
        ]);

        $technician->update($validatedData);

        return response()->json($technician);
    }

    //BORRAR POR ID
    public function destroy(string $id)
    {
        $technician = Technician::findOrFail($id);
        $technician->delete();

        return response()->json(null, 204);
    }
}
