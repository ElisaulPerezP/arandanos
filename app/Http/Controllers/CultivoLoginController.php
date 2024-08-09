<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Cultivo;
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
            //$user = User::where('email', $request->email)->first();
            $user = auth()->user();
            $currentDateTime = Carbon::now();
            

            // Si el usuario ya tiene un cultivo registrado, redirigir con un mensaje de error
            if (Cultivo::exists()) {
                $cultivo = Cultivo::first();
                $cultivo->update([
                    'nombre' => $request->cultivo,
                    'api_token' => $token,
                ]);
                $user->update([
                    'cultivo_registrado_at' => $currentDateTime,  // Almacena la fecha de registro del cultivo
                    'token_adquirido_at' => $currentDateTime,     // Almacena la fecha de adquisición del token
                ]);
                return redirect()->route('dashboard')->withErrors(['msg' => 'Token actualizado']);
            }else {
                // Crear un nuevo registro de cultivo si no existe
                Cultivo::create([
                    'nombre' => $request->cultivo,
                    'api_token'  => $token,
                ]);
                return redirect()->route('dashboard')->with('success', 'Autenticación exitosa!');
            }
        } 
        return redirect()->route('dashboard')->with('error', 'Error de autenticacion.');
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

            // Encuentra el usuario autenticado
            $user = auth()->user();
            $cultivo = Cultivo::first();
            $currentDateTime = Carbon::now();

            // Actualiza su token y nombre del cultivo
            $user->update([
                'cultivo_registrado_at' => $currentDateTime,  // Almacena la fecha de registro del cultivo
                'token_adquirido_at' => $currentDateTime,     // Almacena la fecha de adquisición del token
            ]);

            $cultivo->update([
                'nombre' => $request->cultivo,
                'api_token'  => $token,
                'estado_id' => "2"
            ]);
            // Redirigir al dashboard con un mensaje de éxito
            return redirect()->route('dashboard')->with('success', 'Cultivo actualizado exitosamente!');
        }
    }
}
