<x-app-layout>
    <x-slot name="title">My Dashboard — Chess Puzzle Challenge</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-2xl font-bold text-stone-900">My Dashboard</h1>
    </x-slot>

    <div class="py-4">
        <div class="bg-white rounded-2xl shadow-warm p-8 border border-stone-100 text-center">
            <p class="text-5xl mb-4">♟</p>
            <h2 class="font-display text-2xl font-bold text-stone-900 mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-stone-500">Your challenges and progress will appear here.</p>
            <a href="{{ url('/challenges') }}" class="btn btn-primary mt-6">Browse Challenges →</a>
        </div>
    </div>
</x-app-layout>
