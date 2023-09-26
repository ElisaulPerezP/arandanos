<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Botones para controlar el LED -->
                    <form action="{{ route('led.on') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender LED</button>
                    </form>

                    <form action="{{ route('led.off') }}" method="GET">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Apagar LED</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
