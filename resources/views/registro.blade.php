<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight justify-content-center">
            {{ __('INSCRIBIR CULTIVO') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Mostrar mensajes de error -->
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600">
                                {{ __('¡Ups! Algo salió mal.') }}
                            </div>
                            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Mostrar mensajes de éxito -->
                    @if (session('success'))
                        <div class="mb-4 text-sm font-medium text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('cultivo.update') }}">
                        @csrf  <!-- Token CSRF para protección contra ataques -->
                        
                        <!-- Campo para el Email del Usuario -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email del Usuario</label>
                            <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('email', auth()->user()->email) }}">
                        </div>

                        <!-- Campo para el Nombre del Cultivo -->
                        <div class="mt-4">
                            <label for="cultivo" class="block text-sm font-medium text-gray-700">Nombre del Cultivo</label>
                            <input type="text" name="cultivo" id="cultivo" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('cultivo', auth()->user()->cultivo_nombre) }}">
                        </div>

                        <!-- Campo para la Contraseña -->
                        <div class="mt-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <!-- Botón para enviar el formulario -->
                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Actualizar Cultivo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
