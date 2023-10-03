<x-app-layout>
    <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight justify-content-center">
                {{ __('CONTROL MANUAL') }}
            </h2>
    </x-slot>
    <x-manual-control :buttons="$buttons" />
</x-app-layout>