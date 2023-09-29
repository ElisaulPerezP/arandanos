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

                    <form action="{{ route('Button.on_1_2') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender Camellon 1 y 2</button>
                    </form>

                    <form action="{{ route('Button.on_3_4') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender Camellon 3 y 4</button>
                    </form>

                    <form action="{{ route('Button.on_5_6') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender Camellon 5 y 6</button>
                    </form>

                    <form action="{{ route('Button.on_7_8') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender Camellon 7 y 8</button>
                    </form>

                    <form action="{{ route('Button.on_9_10') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender Camellon 9 y 10</button>
                    </form>

                    <form action="{{ route('Button.off_1_2') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar Camellon 1 y 2</button>
                    </form>

                    <form action="{{ route('Button.off_3_4') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar Camellon 3 y 4</button>
                    </form>

                    <form action="{{ route('Button.off_5_6') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar Camellon 5 y 6</button>
                    </form>

                    <form action="{{ route('Button.off_7_8') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar Camellon 7 y 8</button>
                    </form>

                    <form action="{{ route('Button.off_9_10') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar Camellon 9 y 10</button>
                    </form>

                    <form action="{{ route('Button.STOP') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar TODO </button>
                    </form>

                    <form action="{{ route('Button.on_pump_1') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender bomba 1 </button>
                    </form>

                    <form action="{{ route('Button.off_pump_1') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar bomba 1 </button>
                    </form>

                    <form action="{{ route('Button.on_pump_2') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Encender bomba 2 </button>
                    </form>

                    <form action="{{ route('Button.off_pump_2') }}" method="GET" class="mb-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Apagar bomba 2 </button>
                    </form>

                </div>
                <!-- Mostrar el mensaje -->
                @if(isset($mensaje))
                    <p>{{ $mensaje }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
