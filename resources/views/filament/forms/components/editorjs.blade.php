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
    x-init="init()"
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
                statePath: config.statePath,
                editorId: config.editorId,
                data: config.initialData,
                lastSerialized: null,
                flushing: false,
                initialized: false,
                saveTimer: null,

                async init() {
                    if (this.initialized) {
                        return;
                    }
                    this.initialized = true;

                    await this.ensureEditorJs();

                    // Tear down any lingering editor instance for this holder
                    if (window.__editorJsInstances[this.editorId]) {
                        try {
                            await window.__editorJsInstances[this.editorId].destroy();
                        } catch (e) { /* noop */ }
                        delete window.__editorJsInstances[this.editorId];
                    }

                    this.editor = window.createChallengeEditor({
                        holder: this.editorId,
                        data: this.parseData(this.data),
                        onChange: () => this.handleChange(),
                    });

                    this.lastSerialized = JSON.stringify(this.parseData(this.data));
                    window.__editorJsInstances[this.editorId] = this.editor;

                    this.bindSubmitFlush();
                },

                handleChange() {
                    if (! this.editor || this.flushing) { return; }

                    clearTimeout(this.saveTimer);
                    this.saveTimer = setTimeout(() => this.flush(), 300);
                },

                bindSubmitFlush() {
                    const form = this.$root.closest('form');
                    if (! form || form.dataset.editorjsBound === '1') { return; }
                    form.dataset.editorjsBound = '1';
                    form.addEventListener('submit', () => this.flush(), { capture: true });
                },

                async flush() {
                    if (! this.editor) { return; }
                    this.flushing = true;
                    try {
                        const output = await this.editor.save();
                        const serialized = JSON.stringify(output);
                        if (serialized === this.lastSerialized) {
                            return;
                        }
                        this.lastSerialized = serialized;
                        this.$wire.set(this.statePath, serialized, false);
                    } catch (e) {
                        console.warn('[Editor.js] flush save failed:', e);
                    } finally {
                        setTimeout(() => { this.flushing = false; }, 100);
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
