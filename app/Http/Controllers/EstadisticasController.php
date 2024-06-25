<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estado;
use App\Models\Cultivo;
use App\Events\CultivoInactivo;

class EstadisticasController extends Controller
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        $estado = Estado::create($validated);
    
        return redirect()->route('estados.index')->with('success', 'Estado creado con éxito.');
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
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
        ]);

        $estado->update($validated);
    
        return redirect()->route('estados.index')->with('success', 'Estado actualizado con éxito.');
    }

    public function destroy(Estado $estado)
    {
        $estado->delete();
        return redirect()->route('estados.index')->with('success', 'Estado eliminado con éxito.');
    }

    public function cambiarEstado(Request $request)
    {
        $request->validate([
            'cultivo_id' => 'required|integer|exists:cultivos,id',
        ]);

        $cultivo = Cultivo::find($request->input('cultivo_id'));

        if ($cultivo && $cultivo->estado->nombre == 'Activo') {
            event(new CultivoInactivo($cultivo));
            return response()->json(['success' => true, 'message' => 'Estado cambiado a inactivo.']);
        }

        return response()->json(['success' => false, 'message' => 'Estado no cambiado.']);
    }
}
