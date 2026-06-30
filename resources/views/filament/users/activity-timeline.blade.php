@php
    $events = $events ?? [];
@endphp

<div class="fi-user-activity-timeline">
    <x-filament::section>
        <x-slot:heading>
            Activity Timeline
        </x-slot:heading>

        <x-slot:description>
            A chronological feed of this user's orders, payments, puzzle solves, challenge completions, medals earned, and shipments, derived from platform history.
        </x-slot:description>

        @if (empty($events))
            <x-filament::empty-state icon="heroicon-o-clock">
                <x-slot:heading>
                    No activity yet
                </x-slot:heading>
                <x-slot:description>
                    This user has not placed orders, enrolled in challenges, or solved puzzles yet.
                </x-slot:description>
            </x-filament::empty-state>
        @else
            <ol class="space-y-4">
                @foreach ($events as $event)
                    <li class="flex gap-3">
                        <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-{{ $event['color'] }}-100 text-{{ $event['color'] }}-600 dark:bg-{{ $event['color'] }}-900/40 dark:text-{{ $event['color'] }}-400">
                            <x-filament::icon icon="{{ $event['icon'] }}" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $event['title'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $event['description'] }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                @if (! empty($event['datetime']))
                                    {{ $event['datetime'] }} ({{ $event['when'] ?? '' }})
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-filament::section>
</div>
