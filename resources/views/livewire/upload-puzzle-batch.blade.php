<div class="space-y-6" x-data>
    {{-- Info Banner --}}
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.9-1A5.5 5.5 0 0118 16M9 13l3-3 3 3m-3-3v9" />
            </svg>
            <div class="text-sm text-emerald-800">
                <p class="font-medium">Upload a small CSV batch directly from your machine.</p>
                <p class="mt-1 text-emerald-700">
                    Files up to 5MB. Format: Lichess columns
                    <code class="rounded bg-emerald-100 px-1 text-xs">PuzzleId, FEN, Moves, Rating, RatingDeviation, Popularity, NbPlays, Themes, GameUrl</code>.
                    Overlapping puzzles are skipped automatically.
                </p>
            </div>
        </div>
    </div>

    {{-- Upload Step --}}
    @if (! $storedPath)
        <x-filament-section heading="Select CSV File">
            <div class="flex flex-wrap items-center gap-4">
                <input
                    type="file"
                    accept=".csv,.txt,text/csv"
                    wire:upload="csvFile"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700"
                >
                <div wire:loading wire:target="csvFile" class="flex items-center gap-2 text-sm text-emerald-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Uploading...
                </div>
            </div>
            @error('csvFile')
                <div class="mt-3 text-sm text-red-600">{{ $message }}</div>
            @enderror
        </x-filament-section>
    @endif

    {{-- Preview / Import Step --}}
    @if ($storedPath && ! $imported)
        <x-filament-section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span>{{ $storedName }}</span>
                    @if ($totalRows >= 0)
                        <x-filament::badge size="sm" color="success">
                            {{ number_format($totalRows) }} rows
                        </x-filament::badge>
                    @endif
                </div>
            </x-slot>

            <x-slot name="actions">
                <x-filament::button tag="button" color="gray" size="sm" wire:click="clearFile">
                    Remove
                </x-filament::button>
            </x-slot>

            <div class="mb-3 text-xs text-gray-500">
                {{ $this->formattedSize }} &middot; Sample of first 5 rows shown below
            </div>

            {{-- Sample Table --}}
            @if (! empty($sampleRows))
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">FEN</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rating</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Themes</th>
                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pop.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sampleRows as $row)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-gray-600">
                                        {{ $row['lichess_id'] }}
                                    </td>
                                    <td class="px-3 py-2 font-mono text-xs text-gray-600 truncate max-w-xs" title="{{ $row['fen'] }}">
                                        {{ \Illuminate\Support\Str::limit($row['fen'], 50) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm font-semibold">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                            @if ($row['rating'] >= 2000) bg-purple-100 text-purple-800
                                            @elseif ($row['rating'] >= 1500) bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $row['rating'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (explode(' ', $row['themes']) as $theme)
                                                @if ($theme)
                                                    <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                                        {{ $theme }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                        {{ $row['popularity'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center text-gray-400">
                    No readable rows found. Verify the CSV matches the Lichess 9-column format.
                </div>
            @endif

            {{-- Import Action --}}
            <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-4">
                <x-filament::button
                    tag="button"
                    color="success"
                    size="lg"
                    wire:click="importToDb"
                    wire:loading.attr="disabled"
                    onclick="return confirm('Import {{ number_format($totalRows) }} puzzles into the database?')"
                >
                    <span wire:loading.remove wire:target="importToDb">Import to Database</span>
                    <span wire:loading wire:target="importToDb" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Importing...
                    </span>
                </x-filament::button>
                @if ($totalRows > 0)
                    <span class="text-xs text-gray-400">
                        Imports up to {{ number_format($totalRows) }} puzzles. Duplicates are skipped.
                    </span>
                @endif
            </div>
        </x-filament-section>
    @endif

    {{-- Result Step --}}
    @if ($imported)
        <x-filament-section heading="Import complete" color="success">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-6 w-6 shrink-0 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-gray-700">
                        <strong class="font-mono">{{ number_format($importedCount) }}</strong> puzzles imported.
                        <strong class="font-mono">{{ number_format($skippedCount) }}</strong> duplicates skipped.
                    </p>
                    <div class="mt-4">
                        <x-filament::button
                            tag="button"
                            color="success"
                            wire:click="clearFile"
                        >
                            Upload another batch
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament-section>
    @endif
</div>
