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
        Content is stored as Editor.js JSON blocks.
    </p>
</div>

<script>
    if (! window.editorJsField) {
        window.editorJsField = function(config) {
            return {
                editor: null,
                statePath: config.statePath,
                editorId: config.editorId,
                data: config.initialData,

                async init() {
                    await this.ensureEditorJs();

                    const parsedData = this.parseData(this.data);

                    this.editor = new window.EditorJS({
                        holder: this.editorId,
                        data: parsedData,
                        placeholder: 'Write challenge content here...',
                        tools: {
                            header: {
                                class: window.Header,
                                inlineToolbar: ['link'],
                            },
                            list: {
                                class: window.EditorjsList || window.List,
                                inlineToolbar: true,
                            },
                            paragraph: {
                                class: window.Paragraph,
                                inlineToolbar: true,
                            },
                        },
                        onChange: async () => {
                            if (! this.editor) {
                                return;
                            }

                            const output = await this.editor.save();
                            $wire.set(this.statePath, output);
                        },
                    });
                },

                parseData(data) {
                    if (! data) {
                        return { blocks: [] };
                    }

                    if (typeof data === 'string') {
                        try {
                            return JSON.parse(data);
                        } catch (e) {
                            return { blocks: [] };
                        }
                    }

                    return data;
                },

                async ensureEditorJs() {
                    if (window.EditorJS && window.Header && (window.EditorjsList || window.List) && window.Paragraph) {
                        return;
                    }

                    await this.loadScript('https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.8');
                    await this.loadScript('https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8');
                    await this.loadScript('https://cdn.jsdelivr.net/npm/@editorjs/list@1.9.0');
                    await this.loadScript('https://cdn.jsdelivr.net/npm/@editorjs/paragraph@2.11.6');
                },

                loadScript(src) {
                    return new Promise((resolve, reject) => {
                        const existing = document.querySelector(`script[src="${src}"]`);

                        if (existing) {
                            if (existing.dataset.loaded === 'true') {
                                resolve();
                            } else {
                                existing.addEventListener('load', () => resolve(), { once: true });
                                existing.addEventListener('error', () => reject(new Error('Failed to load script')), { once: true });
                            }

                            return;
                        }

                        const script = document.createElement('script');
                        script.src = src;
                        script.async = true;
                        script.onload = () => {
                            script.dataset.loaded = 'true';
                            resolve();
                        };
                        script.onerror = () => reject(new Error('Failed to load script: ' + src));

                        document.head.appendChild(script);
                    });
                },
            };
        };
    }
</script>
