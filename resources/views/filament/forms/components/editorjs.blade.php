@php
    $statePath = $getStatePath();
    $editorId = str_replace(['.', '[', ']'], '_', $statePath) . '_editor';
@endphp

<div
    x-data="editorJsField({
        statePath: @js($statePath),
        editorId: @js($editorId),
        initialData: @js($getState()),
    })"
    x-init="init(); () => cleanup()"
    class="space-y-3"
>
    <div :id="editorId" class="min-h-[540px] rounded-xl border border-gray-200 bg-white p-4"></div>

    <p class="text-sm text-gray-500">
        Content is stored as Editor.js JSON blocks. Click <strong>Save</strong> to persist changes.
    </p>
</div>

<script>
    window.__editorJsInstances = window.__editorJsInstances || {};

    if (! window.editorJsField) {
        window.editorJsField = function(config) {
            return {
                editor: null,
                wire: null,
                statePath: config.statePath,
                editorId: config.editorId,
                data: config.initialData,
                lastSerialized: null,
                flushing: false,

                async init() {
                    await this.ensureEditorJs();

                    this.wire = this.$wire;

                    // Tear down any lingering editor instance for this holder
                    // (can happen after a Livewire morph re-initialises the component).
                    if (window.__editorJsInstances[this.editorId]) {
                        try {
                            await Promise.resolve(window.__editorJsInstances[this.editorId].destroy());
                        } catch (e) { /* noop */ }
                        delete window.__editorJsInstances[this.editorId];
                        this.editor = null;
                    }

                    this.editor = window.createChallengeEditor({
                        holder: this.editorId,
                        data: this.parseData(this.data),
                        onChange: async () => {
                            if (! this.editor) { return; }

                            try {
                                const output = await this.editor.save();
                                const serialized = JSON.stringify(output);
                                this.lastSerialized = serialized;

                                // Send a JSON string so Livewire's wire snapshot can diff the
                                // value reliably and the Challenge model's `array` cast re-decodes
                                // it server-side. `defer: false` forces the round-trip to commit.
                                this.wire.set(this.statePath, serialized, false);
                            } catch (e) {
                                console.warn('[Editor.js] onChange save failed:', e);
                            }
                        },
                    });

                    this.lastSerialized = JSON.stringify(this.parseData(this.data));
                    window.__editorJsInstances[this.editorId] = this.editor;

                    // When the form is submitted (Filament wires the Save button to a
                    // Livewire call that walks up to the wrapping <form>), flush the
                    // latest editor content to the wire state BEFORE the request fires.
                    this.bindSubmitFlush();

                    // Re-sync from wire state when it changes externally (e.g. after save).
                    this.$watch('$wire.data.' + this.statePath, (value) => {
                        if (! this.editor || this.flushing) { return; }

                        const incoming = this.parseData(value);
                        if (! incoming || ! Array.isArray(incoming.blocks)) { return; }

                        const incomingSerialized = JSON.stringify(incoming);
                        if (incomingSerialized === this.lastSerialized) { return; }

                        // Re-render with the persisted state — safe to do while idle.
                        try {
                            this.editor.blocks.render({ blocks: incoming.blocks });
                            this.lastSerialized = incomingSerialized;
                        } catch (e) { /* noop */ }
                    });
                },

                bindSubmitFlush() {
                    const form = this.$root.closest('form');
                    if (! form || form.dataset.editorjsBound === '1') { return; }
                    form.dataset.editorjsBound = '1';
                    form.addEventListener('submit', () => this.flush(), { capture: true });
                },

                async flush() {
                    if (! this.editor || this.flushing) { return; }
                    this.flushing = true;
                    try {
                        const output = await this.editor.save();
                        const serialized = JSON.stringify(output);
                        this.lastSerialized = serialized;
                        this.wire.set(this.statePath, serialized, false);
                    } catch (e) {
                        console.warn('[Editor.js] flush save failed:', e);
                    }
                    finally {
                        setTimeout(() => { this.flushing = false; }, 300);
                    }
                },

                cleanup() {
                    if (this.editor && typeof this.editor.destroy === 'function') {
                        try { this.editor.destroy(); } catch (e) { /* noop */ }
                    }
                    this.editor = null;
                    if (window.__editorJsInstances) {
                        delete window.__editorJsInstances[this.editorId];
                    }
                },

                parseData(data) {
                    if (! data) { return { blocks: [] }; }
                    if (typeof data === 'string') {
                        try {
                            data = JSON.parse(data);
                        } catch (e) { return { blocks: [] }; }
                    }
                    if (data && Array.isArray(data.blocks)) {
                        data.blocks = data.blocks.map((block) => {
                            if (block.type === 'list' && Array.isArray(block.data?.items)) {
                                const needsMigration = block.data.items.some(
                                    (item) => typeof item === 'string'
                                );
                                if (needsMigration) {
                                    block.data.items = block.data.items.map((item) => {
                                        if (typeof item === 'string') {
                                            return { content: item, items: [] };
                                        }
                                        return item;
                                    });
                                }
                            }
                            return block;
                        });
                    }
                    return data;
                },

                async ensureEditorJs() {
                    if (window.createChallengeEditor) {
                        return;
                    }

                    let attempts = 0;
                    while (attempts < 50) {
                        if (window.createChallengeEditor) { return; }
                        await new Promise((resolve) => setTimeout(resolve, 100));
                        attempts++;
                    }

                    throw new Error('Editor.js bundle (filament-editorjs) failed to load.');
                },
            };
        };
    }
</script>
