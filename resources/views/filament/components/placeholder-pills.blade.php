<div class="flex flex-wrap gap-2">
    @foreach ($placeholders as $placeholder)
        <button
            type="button"
            class="fi-badge fi-badge-size-md flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 bg-gray-50 text-gray-600 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 hover:bg-gray-100 dark:hover:bg-gray-400/20"
            wire:click="insertPlaceholder('{{ $placeholder }}')"
        >
            {{ $placeholder }}
        </button>
    @endforeach
</div>
