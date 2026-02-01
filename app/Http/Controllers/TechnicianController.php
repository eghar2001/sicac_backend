<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use Illuminate\Http\Request;

class TechnicianController extends Controller
{
    use \App\Http\Traits\CanLoadRelationship;

    protected $relations = ['availableShifts'];

    public function __construct()
    {
        $this->authorizeResource(Technician::class, 'technician');
    }

    //GETALL
    public function index()
    {
        $technicians = $this->loadRelationship(Technician::with('user'))->get();
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
        $technician = $this->loadRelationship(Technician::with('user')->where('id', $id))->firstOrFail();
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
