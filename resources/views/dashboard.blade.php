<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Botón para encender el LED -->
                <form action="{{ route('led.on') }}" method="GET">
                    @csrf
                    <button type="submit">Encender LED</button>
                </form>

                <!-- Botón para apagar el LED -->
                <form action="{{ route('led.off') }}" method="GET">
                    @csrf
                    <button type="submit">Apagar LED</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
