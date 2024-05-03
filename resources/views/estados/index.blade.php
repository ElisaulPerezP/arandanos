<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight justify-content-center">
            {{ __('Lista de Estados de Cultivos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Tabla para mostrar estados -->
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                @for ($i = 1; $i <= 12; $i++)
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Solenoide {{ $i }}
                                    </th>
                                @endfor
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bomba 1
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bomba 2
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bomba Fertilizante
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($estados as $estado)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $estado->id }}
                                    </td>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $estado->{'solenoide_'.$i} ? 'ON' : 'OFF' }}
                                        </td>
                                    @endfor
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $estado->bomba_1 ? 'ON' : 'OFF' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $estado->bomba_2 ? 'ON' : 'OFF' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $estado->bomba_fertilizante ? 'ON' : 'OFF' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
