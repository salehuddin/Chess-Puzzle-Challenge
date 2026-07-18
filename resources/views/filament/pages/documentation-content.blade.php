<div class="flex flex-col gap-6 lg:flex-row">
    {{-- Sidebar --}}
    <div class="w-full shrink-0 lg:w-80">
        <div class="cpc-doc-panel">
            <div class="mb-6">
                <div class="flex items-center gap-2 cpc-doc-panel__title-row">
                    <x-heroicon-o-book-open class="h-6 w-6 cpc-doc-panel__icon" />
                    <h2 class="text-xl font-bold cpc-doc-panel__title">Documentation</h2>
                </div>
                <p class="mt-1 text-sm cpc-doc-panel__hint">
                    Internal admin reference loaded from markdown files.
                </p>
            </div>

            <nav class="space-y-6">
                @forelse ($categories as $category)
                    <div>
                        <div class="mb-2 flex items-center gap-2 cpc-doc-panel__category-label">
                            <x-heroicon-o-folder class="h-4 w-4" />
                            {{ $category['label'] }}
                        </div>

                        <ul class="space-y-2">
                            @foreach ($category['documents'] as $document)
                                @php
                                    $isActive = $selectedDocument !== null && $selectedDocument['path'] === $document['path'];
                                @endphp

                                <li>
                                    <a
                                        href="{{ request()->url() }}?doc={{ urlencode($document['path']) }}"
                                        @class([
                                            'cpc-doc-link',
                                            'cpc-doc-link--active' => $isActive,
                                        ])
                                    >
                                        <div @class([
                                            'cpc-doc-link__title',
                                            'cpc-doc-link__title--active' => $isActive,
                                        ])>
                                            {{ $document['title'] }}
                                        </div>

                                        @if (filled($document['description']))
                                            <p class="mt-1 line-clamp-2 text-sm cpc-doc-link__desc">
                                                {{ $document['description'] }}
                                            </p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm cpc-doc-panel__hint">
                        No documentation files found.
                    </p>
                @endforelse
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <div class="min-w-0 flex-1">
        <div class="cpc-doc-content">
            @if ($selectedDocument)
                <div class="mb-8">
                    <div class="flex flex-wrap items-center gap-3">
                        @php
                            $selectedCategory = collect($categories)->first(
                                fn ($category) => $category['documents']->contains(
                                    fn ($document) => $document['path'] === $selectedDocument['path']
                                )
                            );
                        @endphp

                        @if ($selectedCategory)
                            <span class="cpc-doc-pill cpc-doc-pill--category">
                                {{ $selectedCategory['label'] }}
                            </span>
                        @endif

                        <span class="cpc-doc-pill">
                            Updated {{ $selectedDocument['updated_at']->format('M j, Y g:i A') }}
                        </span>

                        <span class="cpc-doc-pill cpc-doc-pill--mono">
                            {{ $selectedDocument['path'] }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <x-heroicon-o-document-text class="h-8 w-8 cpc-doc-panel__icon" />
                        <h1 class="cpc-doc-content__heading">
                            {{ $selectedDocument['title'] }}
                        </h1>
                    </div>
                </div>

                <div class="cpc-doc-prose">
                    {!! $selectedDocument['content'] !!}
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <x-heroicon-o-document-text class="h-12 w-12 cpc-doc-panel__hint" />
                    <h3 class="mt-4 text-lg font-semibold cpc-doc-panel__title">
                        No documentation selected
                    </h3>
                    <p class="mt-1 text-sm cpc-doc-panel__hint">
                        Select a document from the sidebar to view it.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
