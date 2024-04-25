<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;  // Asegúrate de importar tu modelo User

class CultivoLoginController extends Controller
{
    public function login(Request $request)
    {
        $url = env('API_URL') . '/api/login';  // Obtiene la URL de la variable de entorno
    
        // Envía una solicitud POST a la API
        $response = Http::asForm()->post($url, [
            'email' => $request->email,
            'password' => $request->password,
            'cultivo_nombre' => $request->cultivo,
        ]);
    
        // Aquí puedes manejar la respuesta como desees
        if ($response->successful()) {
            $token = $response->json()['token'];  // Extrae el token del JSON de respuesta
    
            // Encuentra el usuario por email
            $user = User::where('email', $request->email)->first();
    
            // Si el usuario existe, actualiza su token y nombre del cultivo
            if ($user) {
                $user->update([
                    'api_token' => $token,                   // Almacena el token
                    'cultivo_nombre' => $request->cultivo    // Almacena el nombre del cultivo
                ]);
            } else {
                // Opcional: crear el usuario si no existe (dependiendo de tu lógica de negocio)
                $user = User::create([
                    'email' => $request->email,
                    'password' => bcrypt($request->password), // Asegúrate de encriptar la contraseña
                    'api_token' => $token,
                    'cultivo_nombre' => $request->cultivo
                ]);
            }
    
            // Redirigir al dashboard con un mensaje de éxito
            return redirect()->route('dashboard')->with('success', 'Autenticación exitosa!');
        } else {
            return back()->withErrors(['msg' => 'Error en la autenticación']);
        }
    }
}
    