<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index()
    {
        $estados = Estado::all();
        return view('estados.index', compact('estados'));
    }

    public function create()
    {
        return view('estados.create');
    }

    public function store(Request $request)
    {
        // Validación de la entrada del usuario
        $validated = $request->validate([
            'solenoide_1' => 'required|boolean',
            'solenoide_2' => 'required|boolean',
            'solenoide_3' => 'required|boolean',
            'solenoide_4' => 'required|boolean',
            'solenoide_5' => 'required|boolean',
            'solenoide_6' => 'required|boolean',
            'solenoide_7' => 'required|boolean',
            'solenoide_8' => 'required|boolean',
            'solenoide_9' => 'required|boolean',
            'solenoide_10' => 'required|boolean',
            'solenoide_11' => 'required|boolean',
            'solenoide_12' => 'required|boolean',
            'bomba_1' => 'required|boolean',
            'bomba_2' => 'required|boolean',
            'bomba_fertilizante' => 'required|boolean',
            'id_tabla_flujos' => 'integer|exists:flujos,id'
        ]);
    
        // Crear y guardar el nuevo estado
        $estado = Estado::create($validated);
    
        // Retornar una respuesta JSON
        return response()->json([
            'success' => true,
            'message' => 'Estado creado con éxito.',
            'data' => $estado
        ], 201); // HTTP status code 201 significa "Created"
    }
    public function show(Estado $estado)
    {
        return view('estados.show', compact('estado'));
    }

    public function edit(Estado $estado)
    {
        return view('estados.edit', compact('estado'));
    }

    public function update(Request $request, Estado $estado)
    {
        $request->validate([
            'solenoide_1' => 'required|boolean',
        ]);
        $estado->update($request->all());
        return redirect()->route('estados.index')->with('success', 'Estado actualizado con éxito.');
    }

    public function destroy(Estado $estado)
    {
        $estado->delete();
        return redirect()->route('estados.index')->with('success', 'Estado eliminado con éxito.');
    }
}
