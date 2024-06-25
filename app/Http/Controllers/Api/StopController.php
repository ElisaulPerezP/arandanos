<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cultivo;
use App\Events\CultivoInactivo;

class StopController extends Controller
{
    public function stop(Request $request)
    {
        // Asumiendo que tienes un cultivo específico que necesitas marcar como inactivo
        $cultivo = Cultivo::find($request->input('cultivo_id'));

        if ($cultivo) {
            // Emitir el evento
            event(new CultivoInactivo($cultivo));

            return response()->json(['message' => 'Process stopped successfully']);
        }

        return response()->json(['message' => 'Cultivo not found'], 404);
    }
}
