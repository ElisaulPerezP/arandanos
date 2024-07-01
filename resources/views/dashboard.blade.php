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
                    @if($cultivo)
                            <p>{{ __('Cultivo registrado:') }} {{ $cultivo->nombre }}</p>
                            <p>{{ __('Coordenadas:') }} {{ $cultivo->coordenadas }}</p>
                            <p>{{ __('Estado Actual:') }} {{ $cultivo->estadoActual->nombre ?? 'N/A' }}</p>
                            <p>{{ __('Comando Actual:') }} {{ $cultivo->comandoActual->nombre ?? 'N/A' }}</p>
                        @else
                            <p>{{ __('No hay un cultivo registrado.') }}</p>
                        @endif
                    </div>

                    <!-- Botón para actualizar cultivo -->
                    @if ($cultivo)
                        <div class="mt-6">
                            <a href="{{ route('update.registro') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Actualizar Cultivo') }}
                            </a>
                        </div>
                    @endif

                    <!-- Botón para iniciar el sistema si el estado es inactivo -->
                    @if ($cultivo && (!$cultivo->estadoActual || $cultivo->estadoActual->nombre === 'Inactivo'))
                        <div class="mt-6">
                            <a href="{{ url('system/start') }}" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Iniciar Sistema') }}
                            </a>
                        </div>
                    @endif
                    <!-- Botón para deterner el sistema si el estado es Aactivo -->

                    @if ($cultivo && $cultivo->estadoActual->nombre === 'Activo')
                        <div class="mt-6">
                            <a href="{{ url('system/stop') }}" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Detener Sistema') }}
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
