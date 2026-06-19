<?php

use Livewire\Component;
use App\Models\Challenge;
use App\Models\Bundle;

new class extends Component
{
    public $filter = 'all';

    public function with(): array
    {
        $challengesQuery = Challenge::active();
        
        if ($this->filter !== 'all') {
            // Filter by logic: check if name contains keyword
            $challengesQuery->where('name', 'like', "%{$this->filter}%");
        }

        return [
            'challenges' => $challengesQuery->get(),
            'bundles' => Bundle::active()->with('challenges')->get(),
        ];
    }
};
?>
<x-marketing-layout>
    <x-slot name="title">Browse Challenges — Chess Puzzle Challenge</x-slot>

    {{-- Hero Sector --}}
    <div class="bg-base-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="font-display text-4xl lg:text-5xl font-black text-stone-900 mb-4">Browse Challenges</h1>
            <p class="text-lg text-stone-500 max-w-2xl mx-auto">
                Filter by difficulty or grab a bundle deal for multiple series. 
                Complete a challenge to earn its physical medal.
            </p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        {{-- Filters (Livewire Reactivity) --}}
        <div class="flex flex-wrap gap-2 justify-center mb-12">
            <button wire:click="$set('filter', 'all')" class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline btn-primary' }} rounded-full px-6">
                All challenges
            </button>
            <button wire:click="$set('filter', 'beginner')" class="btn {{ $filter === 'beginner' ? 'btn-success text-white' : 'btn-outline btn-success' }} rounded-full px-6 gap-2">
                🌱 Beginner
            </button>
            <button wire:click="$set('filter', 'intermediate')" class="btn {{ $filter === 'intermediate' ? 'btn-warning text-stone-900' : 'btn-outline btn-warning' }} rounded-full px-6 gap-2">
                ⚡ Intermediate
            </button>
            <button wire:click="$set('filter', 'advanced')" class="btn {{ $filter === 'advanced' ? 'btn-error text-white' : 'btn-outline btn-error' }} rounded-full px-6 gap-2">
                🔥 Advanced
            </button>
        </div>

        {{-- Challenges Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            @forelse($challenges as $challenge)
                @php
                    $levelData = match(true) {
                        str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'badge-success'],
                        str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'badge-warning'],
                        str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'badge-error'],
                        default => ['♟', 'Challenge', 'badge-primary'],
                    };
                    $rules = $challenge->rules ?? [];
                @endphp
                <div wire:key="challenge-{{ $challenge->id }}" class="bg-white rounded-2xl shadow-warm overflow-hidden border border-stone-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                    <div class="bg-chess-pattern h-24 relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/80"></div>
                        <div class="absolute bottom-3 left-5">
                            <span class="badge {{ $levelData[2] }} badge-sm gap-1 font-semibold">
                                {{ $levelData[0] }} {{ $levelData[1] }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="font-display text-xl font-bold text-stone-900 mb-2">{{ $challenge->name }}</h3>
                        <p class="text-stone-500 text-sm mb-4 line-clamp-3 flex-1">{{ $challenge->description }}</p>

                        <div class="grid grid-cols-2 gap-y-2 mb-5 text-sm text-stone-600">
                            <div class="flex items-center gap-2"><svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> <strong>{{ $challenge->puzzle_count }}</strong> puzzles</div>
                            <div class="flex items-center gap-2"><span>🏅</span> Physical medal</div>
                        </div>

                        <div class="flex items-center justify-between mb-5 pt-4 border-t border-stone-100">
                            <div>
                                <p class="text-2xl font-black text-stone-900">MYR {{ number_format($challenge->price_myr, 2) }}</p>
                                <p class="text-xs text-stone-400">or USD {{ number_format($challenge->price_usd, 2) }}</p>
                            </div>
                        </div>

                        <a href="{{ url('/challenges/'.$challenge->slug) }}" class="btn btn-primary w-full gap-2">
                            View Details →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <p class="text-3xl mb-4">🤷‍♂️</p>
                    <p class="text-stone-500 font-medium">No challenges found for this filter.</p>
                </div>
            @endforelse
        </div>

        {{-- Bundles --}}
        <div id="bundles" class="mb-12">
            <div class="text-center mb-10">
                <span class="inline-block text-accent font-semibold text-sm uppercase tracking-widest mb-2">Best Value</span>
                <h2 class="font-display text-4xl font-black text-stone-900">Challenge Bundles</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:px-16">
                @foreach($bundles as $bundle)
                    <div wire:key="bundle-{{ $bundle->id }}" class="bg-white rounded-2xl shadow-warm border border-stone-100 hover:shadow-warm-lg hover:-translate-y-1 transition-all duration-300 p-8 flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center text-xl">🎁</div>
                            <h3 class="font-display text-2xl font-bold text-stone-900">{{ $bundle->name }}</h3>
                        </div>
                        <p class="text-stone-500 text-sm mb-6 flex-1">{{ $bundle->description }}</p>
                        
                        <div class="mb-6 pb-6 border-b border-stone-100">
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-3">Includes</p>
                            <div class="space-y-2">
                                @foreach($bundle->challenges as $c)
                                    <div class="flex items-center gap-2 text-sm font-medium text-stone-700">
                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ $c->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-black text-stone-900">MYR {{ number_format($bundle->price_myr, 2) }}</p>
                                <p class="text-xs text-stone-400">or USD {{ number_format($bundle->price_usd, 2) }}</p>
                            </div>
                            <button class="btn btn-accent px-6">Buy Bundle</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-marketing-layout>