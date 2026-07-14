<x-marketing-layout :title="$title . ' — Docs — Chess Puzzle Challenge'">
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center gap-2 text-sm text-neutral-400">
            <a href="{{ route('docs.index') }}" class="hover:text-primary transition-colors">Docs</a>
            <span>/</span>
            <span class="text-neutral-600 font-medium">{{ $title }}</span>
        </div>

        {{-- Prose content --}}
        <article class="prose prose-neutral max-w-none
            prose-headings:font-display prose-headings:text-neutral-900
            prose-a:text-primary prose-a:no-underline hover:prose-a:underline
            prose-code:text-primary prose-code:bg-primary/5 prose-code:px-2 prose-code:py-0.5 prose-code:rounded prose-code:before:content-none prose-code:after:content-none
            prose-pre:bg-neutral-900 prose-pre:text-neutral-100
            prose-table:text-sm prose-th:bg-neutral-50
            prose-img:rounded-xl
        ">
            {!! $html !!}
        </article>

        {{-- Back link --}}
        <div class="mt-12 pt-6 border-t border-neutral-200">
            <a href="{{ route('docs.index') }}" class="text-sm text-neutral-500 hover:text-primary transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Documentation
            </a>
        </div>
    </div>
</x-marketing-layout>
