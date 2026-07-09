<div class="space-y-6" x-data>
    {{-- Info Banner --}}
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900/40 dark:bg-emerald-900/10">
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.9 5 5 0 019.9-1A5.5 5.5 0 0118 16M9 13l3-3 3 3m-3-3v9" />
            </svg>
            <div class="text-sm text-emerald-800 dark:text-emerald-300">
                <p class="font-medium">Upload a small CSV batch directly from your machine.</p>
                <p class="mt-1 text-emerald-700 dark:text-emerald-400">
                    Files up to 5MB. Format: Lichess columns
                    <code class="rounded bg-emerald-100 px-1 text-xs dark:bg-emerald-900/30">PuzzleId, FEN, Moves, Rating, RatingDeviation, Popularity, NbPlays, Themes, GameUrl</code>.
                    Overlapping puzzles are skipped automatically.
                </p>
            </div>
        </div>
    </div>

    {{-- Upload Step --}}
    @if (! $storedPath)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select CSV File</label>
            <div class="mt-2 flex flex-wrap items-center gap-4">
                <input
                    type="file"
                    accept=".csv,.txt,text/csv"
                    wire:upload="csvFile"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700 dark:text-gray-400"
                >
                <div wire:loading wire:target="csvFile" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Uploading...
                </div>
            </div>
            @error('csvFile')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- Preview / Import Step --}}
    @if ($storedPath && ! $imported)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            {{-- File Summary --}}
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ $storedName }}
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                            {{ $this->formattedSize }} @if ($totalRows >= 0)&middot; {{ number_format($totalRows) }} rows ready @endif
                        </p>
                    </div>
                    <button
                        wire:click="clearFile"
                        class="cursor-pointer rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Remove &amp; pick another
                    </button>
                </div>
            </div>

            {{-- Sample Table --}}
            <div class="overflow-x-auto">
                @if (! empty($sampleRows))
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Lichess ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">FEN</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rating</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Themes</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Popularity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach ($sampleRows as $row)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap px-4 py-2 font-mono text-xs text-gray-600 dark:text-gray-300">
                                        {{ $row['lichess_id'] }}
                                    </td>
                                    <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300 truncate max-w-xs" title="{{ $row['fen'] }}">
                                        {{ \Illuminate\Support\Str::limit($row['fen'], 50) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2 text-sm font-semibold">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                            @if ($row['rating'] >= 2000) bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                            @elseif ($row['rating'] >= 1500) bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">
                                            {{ $row['rating'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (explode(' ', $row['themes']) as $theme)
                                                @if ($theme)
                                                    <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                        {{ $theme }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $row['popularity'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                        No readable rows found. Verify the CSV matches the Lichess 9-column format.
                    </div>
                @endif
            </div>

            {{-- Import Action --}}
            @if (! empty($sampleRows))
                <div class="flex flex-wrap items-center gap-4 border-t border-gray-100 px-6 py-4 dark:border-gray-700">
                    <button
                        wire:click="importToDb"
                        wire:loading.attr="disabled"
                        class="cursor-pointer rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50 dark:focus:ring-offset-gray-800"
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
                    </button>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        @if ($totalRows > 0)
                            Imports up to {{ number_format($totalRows) }} puzzles. Duplicates are skipped.
                        @endif
                    </span>
                </div>
            @endif
        </div>
    @endif

    {{-- Result Step --}}
    @if ($imported)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-900/10">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-6 w-6 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-300">Import complete</h3>
                    <p class="mt-1 text-sm text-emerald-800 dark:text-emerald-400">
                        <strong class="font-mono">{{ number_format($importedCount) }}</strong> puzzles imported.
                        <strong class="font-mono">{{ number_format($skippedCount) }}</strong> duplicates skipped.
                    </p>
                    <div class="mt-4">
                        <button
                            wire:click="clearFile"
                            class="cursor-pointer rounded-lg bg-emerald-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        >
                            Upload another batch
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
