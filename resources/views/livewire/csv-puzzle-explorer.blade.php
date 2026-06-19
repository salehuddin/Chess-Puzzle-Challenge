<div class="space-y-6" x-data>
    {{-- File Path --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    CSV File Path
                </label>
                <input
                    type="text"
                    wire:model="csvPath"
                    placeholder="storage/app/lichess_db_puzzle.csv"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                >
            </div>
            <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">
                Absolute or storage path
            </span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Filters</h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Min Rating</label>
                <input
                    type="number"
                    wire:model.live.debounce.500ms="minRating"
                    placeholder="Any"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                >
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Max Rating</label>
                <input
                    type="number"
                    wire:model.live.debounce.500ms="maxRating"
                    placeholder="Any"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                >
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Min Popularity</label>
                <input
                    type="number"
                    wire:model.live.debounce.500ms="minPopularity"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                >
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Themes</label>
                <div class="mt-1 flex items-center gap-2">
                    @if (count($selectedThemes) > 0)
                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400">
                            {{ count($selectedThemes) }} selected
                        </span>
                    @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">None selected</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Theme Selector --}}
        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Theme Selector</h4>
                <button
                    wire:click="loadThemes"
                    wire:loading.attr="disabled"
                    class="cursor-pointer rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 disabled:cursor-wait disabled:opacity-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700"
                >
                    <span wire:loading.remove wire:target="loadThemes">Load themes from CSV</span>
                    <span wire:loading wire:target="loadThemes">Scanning...</span>
                </button>
            </div>

            @if ($themesLoaded)
                <div class="mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="themeSearch"
                        placeholder="Search themes..."
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white sm:text-sm"
                    >
                </div>

                <div class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-600 dark:bg-gray-800">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        @foreach ($this->filteredThemes as $theme)
                            <label class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-xs text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50">
                                <input
                                    type="checkbox"
                                    wire:click="toggleTheme('{{ $theme }}')"
                                    @if (in_array($theme, $selectedThemes)) checked @endif
                                    class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700"
                                >
                                <span class="truncate" title="{{ $theme }}">{{ $theme }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Click "Load themes from CSV" to scan the file and show available themes.
                </p>
            @endif
        </div>

        {{-- Limit + Random Mode --}}
        <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-gray-100 pt-4 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Limit results:</label>
                <select
                    wire:model.live="limit"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                >
                    @foreach ($limitOptions as $option)
                        <option value="{{ $option }}">{{ number_format($option) }}</option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input
                    type="checkbox"
                    wire:model.live="randomMode"
                    class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700"
                >
                Pick randomly
            </label>
        </div>

        {{-- Match Count Preview --}}
        @if ($matchCount >= 0)
            <div class="mt-4 flex items-center gap-3 rounded-lg bg-emerald-50 px-4 py-3 dark:bg-emerald-900/20">
                <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="text-sm text-emerald-800 dark:text-emerald-300">
                    <strong>{{ number_format($matchCount) }}</strong> puzzles match the current filters.
                </span>
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-3">
            <button
                wire:click="countMatches"
                wire:loading.attr="disabled"
                class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800"
            >
                <span wire:loading.remove wire:target="countMatches">Count matches</span>
                <span wire:loading wire:target="countMatches">Counting...</span>
            </button>

            <button
                wire:click="applyFilters"
                wire:loading.attr="disabled"
                class="cursor-pointer rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50 dark:focus:ring-offset-gray-800"
            >
                <span wire:loading.remove wire:target="applyFilters">Apply Filters</span>
                <span wire:loading wire:target="applyFilters" class="flex items-center gap-1">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Scanning...
                </span>
            </button>

            <button
                wire:click="resetFilters"
                class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
            >
                Reset
            </button>
        </div>
    </div>

    {{-- Results --}}
    @if ($hasSearched)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-3 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    @if ($totalMatches >= 0)
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ number_format($totalMatches) }}</span>
                        matching puzzles
                        @if ($randomMode)
                            (randomly selected)
                        @endif
                    @else
                        Results
                    @endif
                </h3>

                @if ($totalMatches > 0 && !$randomMode)
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Showing {{ number_format(($page - 1) * $perPage + 1) }}-{{ number_format(min($page * $perPage, $totalMatches)) }}
                    </span>
                @endif
            </div>

            @if (empty($rows))
                <div class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                    @if ($streaming)
                        Scanning CSV...
                    @else
                        No puzzles match the current filters.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">FEN</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Rating</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Themes</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Popularity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @foreach ($rows as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="whitespace-nowrap px-4 py-2 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $row['match_index'] + 1 }}
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
                </div>

                {{-- Pagination --}}
                @if ($this->totalPages > 1)
                    <div class="flex items-center justify-between border-t border-gray-100 px-6 py-3 dark:border-gray-700">
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            Page {{ $page }} of {{ $this->totalPages }}
                        </span>
                        <div class="flex gap-1">
                            <button
                                wire:click="goToPage({{ $page - 1 }})"
                                @if ($page <= 1) disabled @endif
                                class="cursor-pointer rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Prev
                            </button>
                            @php
                                $start = max(1, $page - 3);
                                $end = min($this->totalPages, $page + 3);
                            @endphp
                            @for ($p = $start; $p <= $end; $p++)
                                <button
                                    wire:click="goToPage({{ $p }})"
                                    class="cursor-pointer rounded-lg border px-3 py-1 text-xs font-medium
                                        {{ $p === $page
                                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'
                                        }}"
                                >
                                    {{ $p }}
                                </button>
                            @endfor
                            <button
                                wire:click="goToPage({{ $page + 1 }})"
                                @if ($page >= $this->totalPages) disabled @endif
                                class="cursor-pointer rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Actions --}}
        @if (!empty($rows))
            <div class="flex gap-3">
                <button
                    wire:click="exportCsv"
                    class="cursor-pointer rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    Export to CSV
                </button>

                <button
                    wire:click="importToDb"
                    wire:loading.attr="disabled"
                    class="cursor-pointer rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-50 dark:focus:ring-offset-gray-800"
                    onclick="return confirm('Import {{ $randomMode ? count($this->randomPicks) : $totalMatches }} puzzles into the database?')"
                >
                    <span wire:loading.remove wire:target="importToDb">Import to Database</span>
                    <span wire:loading wire:target="importToDb">Importing...</span>
                </button>
            </div>
        @endif
    @endif
</div>
