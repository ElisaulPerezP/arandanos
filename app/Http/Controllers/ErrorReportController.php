<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorReportController extends Controller
{
    public function reportError(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'script_name' => 'required|string',
            'error_message' => 'required|string',
            'timestamp' => 'required|date',
        ]);

        // Registrar el error en el canal de log scripts_errors
        Log::channel('scripts_errors')->error('Error en el script ' . $request->input('script_name'), [
            'error_message' => $request->input('error_message'),
            'timestamp' => $request->input('timestamp'),
        ]);

        return response()->json(['message' => 'Error reportado con éxito'], 200);
    }
}
