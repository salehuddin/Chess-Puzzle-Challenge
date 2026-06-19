<?php

use Livewire\Component;
use App\Models\Challenge;

new class extends Component
{
    public Challenge $challenge;

    public function mount(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }
};
?>
<x-marketing-layout>
    <x-slot name="title">{{ $challenge->name }} — Chess Puzzle Challenge</x-slot>

    @php
        $levelData = match(true) {
            str_contains(strtolower($challenge->name), 'beginner') => ['🌱', 'Beginner', 'text-success'],
            str_contains(strtolower($challenge->name), 'intermediate') => ['⚡', 'Intermediate', 'text-warning'],
            str_contains(strtolower($challenge->name), 'advanced') => ['🔥', 'Advanced', 'text-error'],
            default => ['♟', 'Challenge', 'text-primary'],
        };
        $rules = $challenge->rules ?? [];
        $enrollUrl = route('challenges.enroll', ['challenge' => $challenge], absolute: false);
    @endphp

    <div class="bg-base-200 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 mb-4 font-semibold {{ $levelData[2] }}">
                <span>{{ $levelData[0] }}</span>
                <span class="uppercase tracking-widest text-sm">{{ $levelData[1] }}</span>
            </div>
            
            <h1 class="font-display text-4xl lg:text-5xl font-black text-stone-900 mb-6">{{ $challenge->name }}</h1>
            
            <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-8 flex flex-col md:flex-row gap-8 items-start">
                
                {{-- Details --}}
                <div class="flex-1">
                    <p class="text-stone-600 text-lg leading-relaxed mb-6">{{ $challenge->description }}</p>
                    
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-stone-50 p-4 rounded-xl flex items-center gap-3 border border-stone-100">
                            <span class="text-2xl">♟</span>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase">Puzzles</p>
                                <p class="font-bold text-stone-900">{{ $challenge->puzzle_count }} curated</p>
                            </div>
                        </div>
                        <div class="bg-stone-50 p-4 rounded-xl flex items-center gap-3 border border-stone-100">
                            <span class="text-2xl">🏅</span>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase">Reward</p>
                                <p class="font-bold text-stone-900">Physical Medal</p>
                            </div>
                        </div>
                        <div class="bg-stone-50 p-4 rounded-xl flex items-center gap-3 border border-stone-100">
                            <span class="text-2xl">✦</span>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase">Digital</p>
                                <p class="font-bold text-stone-900">Profile Sticker</p>
                            </div>
                        </div>
                        <div class="bg-stone-50 p-4 rounded-xl flex items-center gap-3 border border-stone-100">
                            <span class="text-2xl">⏱</span>
                            <div>
                                <p class="text-xs font-semibold text-stone-400 uppercase">Pace</p>
                                <p class="font-bold text-stone-900">{{ ($rules['order'] ?? null) === 'sequential' ? 'Sequential' : 'Any Order' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Enrollment Box --}}
                <div class="w-full md:w-80 bg-stone-50 rounded-2xl p-6 border border-stone-200 shrink-0">
                    <p class="text-sm font-semibold text-stone-400 uppercase tracking-widest mb-4">Enrollment</p>
                    
                    <div class="mb-6">
                        <p class="text-lg font-black text-stone-900 mb-1">Join the challenge</p>
                        <p class="text-sm text-stone-500 font-medium tracking-wide">Sign in or register to enroll. Admins enroll instantly without payment.</p>
                    </div>

                    <div class="space-y-3">
                        @guest
                            <a href="{{ route('register', ['redirect_to' => $enrollUrl]) }}" class="btn btn-primary w-full shadow-warm">Register to Enroll</a>
                            <a href="{{ route('login', ['redirect_to' => $enrollUrl]) }}" class="btn btn-outline btn-primary w-full">Sign In</a>
                        @else
                            <a href="{{ $enrollUrl }}" class="btn btn-primary w-full btn-lg gap-2 shadow-warm">
                                {{ auth()->user()->isAdmin() ? 'Enroll as Admin' : 'Enroll Now' }} <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <p class="text-xs text-stone-500 text-center">Admins bypass purchase; regular users start with a pending enrollment.</p>
                        @endguest
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-marketing-layout>