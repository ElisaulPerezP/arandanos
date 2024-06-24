<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Carbon\Carbon;

class CultivoLoginController extends Controller
{
    public function showRegistrationForm()
    {
        $user = auth()->user();

        // Verificar si el usuario ya tiene un cultivo registrado
        if ($user && $user->cultivo_nombre) {
            return redirect()->route('dashboard')->withErrors(['msg' => 'Ya tienes un cultivo registrado.']);
        }

        return view('registro');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'cultivo' => 'required',
        ]);

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

            $currentDateTime = Carbon::now();

            // Si el usuario ya tiene un cultivo registrado, redirigir con un mensaje de error
            if ($user && $user->cultivo_nombre) {
                return redirect()->route('dashboard')->withErrors(['msg' => 'Ya tienes un cultivo registrado.']);
            }

            // Si el usuario existe, actualiza su token y nombre del cultivo
            if ($user) {
                $user->update([
                    'api_token' => $token,                   // Almacena el token
                    'cultivo_nombre' => $request->cultivo,   // Almacena el nombre del cultivo
                    'cultivo_registrado_at' => $currentDateTime,  // Almacena la fecha de registro del cultivo
                    'token_adquirido_at' => $currentDateTime,     // Almacena la fecha de adquisición del token
                ]);
            } else {
                // Opcional: crear el usuario si no existe (dependiendo de tu lógica de negocio)
                $user = User::create([
                    'email' => $request->email,
                    'password' => bcrypt($request->password), // Asegúrate de encriptar la contraseña
                    'api_token' => $token,
                    'cultivo_nombre' => $request->cultivo,
                    'cultivo_registrado_at' => $currentDateTime,
                    'token_adquirido_at' => $currentDateTime,
                ]);
            }

            // Redirigir al dashboard con un mensaje de éxito
            return redirect()->route('dashboard')->with('success', 'Autenticación exitosa!');
        } else {
            return back()->withErrors(['msg' => 'Error en la autenticación']);
        }
    }

    public function showUpdateForm()
    {
        return view('registro');
    }

    public function updateCultivo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'cultivo' => 'required',
        ]);

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
            $user = auth()->user();

            $currentDateTime = Carbon::now();

            // Actualiza su token y nombre del cultivo
            $user->update([
                'api_token' => $token,                   // Almacena el token
                'cultivo_nombre' => $request->cultivo,   // Almacena el nombre del cultivo
                'cultivo_registrado_at' => $currentDateTime,  // Almacena la fecha de registro del cultivo
                'token_adquirido_at' => $currentDateTime,     // Almacena la fecha de adquisición del token
            ]);

            // Redirigir al dashboard con un mensaje de éxito
            return redirect()->route('dashboard')->with('success', 'Cultivo actualizado exitosamente!');
        } else {
            return back()->withErrors(['msg' => 'Error en la actualización del cultivo']);
        }
    }
}
