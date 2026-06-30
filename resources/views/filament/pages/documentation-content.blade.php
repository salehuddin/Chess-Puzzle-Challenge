<div class="flex flex-col gap-6 lg:flex-row">
    {{-- Sidebar --}}
    <div class="w-full shrink-0 lg:w-80">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5">
            <div class="mb-6">
                <div class="flex items-center gap-2 text-gray-900">
                    <x-heroicon-o-book-open class="h-6 w-6 text-primary-600" />
                    <h2 class="text-xl font-bold">Documentation</h2>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Internal admin reference loaded from markdown files.
                </p>
            </div>

            <nav class="space-y-6">
                @forelse ($categories as $category)
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
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
                                            'block rounded-lg border p-3 transition',
                                            'border-primary-600 bg-primary-50 ring-1 ring-primary-600' => $isActive,
                                            'border-gray-200 hover:border-gray-300 hover:bg-gray-50' => ! $isActive,
                                        ])
                                    >
                                        <div @class([
                                            'font-semibold',
                                            'text-primary-700' => $isActive,
                                            'text-gray-900' => ! $isActive,
                                        ])>
                                            {{ $document['title'] }}
                                        </div>

                                        @if (filled($document['description']))
                                            <p class="mt-1 line-clamp-2 text-sm text-gray-500">
                                                {{ $document['description'] }}
                                            </p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">
                        No documentation files found.
                    </p>
                @endforelse
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <div class="min-w-0 flex-1">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 lg:p-8">
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
                            <span class="inline-flex items-center rounded-full bg-primary-600 px-3 py-1 text-xs font-medium text-white">
                                {{ $selectedCategory['label'] }}
                            </span>
                        @endif

                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                            Updated {{ $selectedDocument['updated_at']->format('M j, Y g:i A') }}
                        </span>

                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-mono text-gray-600">
                            {{ $selectedDocument['path'] }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <x-heroicon-o-document-text class="h-8 w-8 text-primary-600" />
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ $selectedDocument['title'] }}
                        </h1>
                    </div>
                </div>

                <div class="documentation-content">
                    {!! $selectedDocument['content'] !!}
                </div>

                @once
                    <style>
                        .documentation-content {
                            color: #374151;
                            line-height: 1.7;
                        }

                        .documentation-content > * + * {
                            margin-top: 1.25em;
                        }

                        .documentation-content h1 {
                            font-size: 1.875rem;
                            font-weight: 800;
                            color: #111827;
                            margin-bottom: 0.75em;
                            margin-top: 1.5em;
                        }

                        .documentation-content h2 {
                            font-size: 1.5rem;
                            font-weight: 700;
                            color: #111827;
                            margin-bottom: 0.5em;
                            margin-top: 1.5em;
                        }

                        .documentation-content h3 {
                            font-size: 1.25rem;
                            font-weight: 600;
                            color: #111827;
                            margin-bottom: 0.5em;
                            margin-top: 1.25em;
                        }

                        .documentation-content p {
                            margin-bottom: 1em;
                        }

                        .documentation-content ul,
                        .documentation-content ol {
                            margin-left: 1.5em;
                            margin-bottom: 1em;
                        }

                        .documentation-content ul {
                            list-style-type: disc;
                        }

                        .documentation-content ol {
                            list-style-type: decimal;
                        }

                        .documentation-content li {
                            margin-bottom: 0.25em;
                        }

                        .documentation-content a {
                            color: #059669;
                            text-decoration: underline;
                        }

                        .documentation-content code {
                            background-color: #f3f4f6;
                            padding: 0.125em 0.375em;
                            border-radius: 0.25em;
                            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                            font-size: 0.875em;
                        }

                        .documentation-content pre {
                            background-color: #1f2937;
                            color: #f9fafb;
                            padding: 1em;
                            border-radius: 0.5em;
                            overflow-x: auto;
                            margin-bottom: 1em;
                        }

                        .documentation-content pre code {
                            background-color: transparent;
                            padding: 0;
                            color: inherit;
                        }

                        .documentation-content blockquote {
                            border-left: 4px solid #d1d5db;
                            padding-left: 1em;
                            color: #4b5563;
                            font-style: italic;
                        }

                        .documentation-content table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 1em;
                        }

                        .documentation-content th,
                        .documentation-content td {
                            border: 1px solid #e5e7eb;
                            padding: 0.625em 0.875em;
                            text-align: left;
                        }

                        .documentation-content th {
                            background-color: #f9fafb;
                            font-weight: 600;
                        }

                        .documentation-content hr {
                            border: 0;
                            border-top: 1px solid #e5e7eb;
                            margin: 2em 0;
                        }
                    </style>
                @endonce
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <x-heroicon-o-document-text class="h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">
                        No documentation selected
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Select a document from the sidebar to view it.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
