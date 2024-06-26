<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight justify-content-center">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Barra de navegación -->
                    <nav class="mb-4">
                        <ul class="flex space-x-4">
                            <li>
                                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-gray-900">
                                    {{ __('Dashboard') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('registro') }}" class="text-gray-700 hover:text-gray-900">
                                    {{ __('Inscribir Cultivo') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('riego') }}" class="text-gray-700 hover:text-gray-900">
                                    {{ __('Administrar Riegos') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('estadisticas') }}" class="text-gray-700 hover:text-gray-900">
                                    {{ __('Ver Estadísticas') }}
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Mostrar mensajes de éxito -->
                    @if (session('success'))
                        <div class="mb-4 text-sm font-medium text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

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

                    <!-- Detalles del cultivo -->
                    <div>
                        <p>{{ __('Cultivo registrado:') }} {{ auth()->user()->cultivo_nombre }}</p>
                        <p>{{ __('Fecha de registro:') }} {{ auth()->user()->cultivo_registrado_at }}</p>
                        <p>{{ __('Estado del cultivo:') }} {{ auth()->user()->cultivo->estadoActual->nombre }}</p>
                    </div>

                    <!-- Botón para actualizar cultivo -->
                    <div class="mt-6">
                        <a href="{{ route('update.registro') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Actualizar Cultivo') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
