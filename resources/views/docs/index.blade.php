<x-marketing-layout title="Documentation — Chess Puzzle Challenge">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="font-display text-4xl font-bold text-stone-900 mb-3">Documentation</h1>
            <p class="text-stone-500 max-w-2xl mx-auto">Guides, feature documentation, and development references for the Chess Puzzle Challenge platform.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Project Guides --}}
            <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-6">
                <h2 class="font-display text-lg font-bold text-stone-900 mb-4 flex items-center gap-2">
                    <span class="text-2xl">📘</span> Project Guides
                </h2>
                <ul class="space-y-2">
                    @foreach($rootDocs as $file => $title)
                        <li>
                            <a href="{{ route('docs.show', $file) }}" class="text-stone-700 hover:text-primary text-sm font-medium transition-colors">
                                {{ $title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Feature Docs --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach($featureDocs as $category => $items)
                    <div class="bg-white rounded-2xl shadow-warm border border-stone-100 p-6">
                        <h2 class="font-display text-lg font-bold text-stone-900 mb-4">{{ $category }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($items as $item)
                                <a href="{{ route('docs.show', $item['path']) }}" class="text-stone-700 hover:text-primary text-sm font-medium transition-colors px-3 py-2 rounded-lg hover:bg-primary/5">
                                    {{ $item['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-marketing-layout>
