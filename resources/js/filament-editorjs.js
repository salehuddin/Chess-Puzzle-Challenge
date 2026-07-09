import EditorJS from '@editorjs/editorjs';
import Paragraph from '@editorjs/paragraph';
import Header from '@editorjs/header';
import NestedList from '@editorjs/nested-list';
import Checklist from '@editorjs/checklist';
import Image from '@editorjs/image';
import Quote from '@editorjs/quote';
import Warning from '@editorjs/warning';
import Delimiter from '@editorjs/delimiter';
import Table from '@editorjs/table';
import Code from '@editorjs/code';
import Raw from '@editorjs/raw';
import Link from '@editorjs/link';
import Attaches from '@editorjs/attaches';
import Embed from '@editorjs/embed';
import Marker from '@editorjs/marker';
import InlineCode from '@editorjs/inline-code';
import Underline from '@editorjs/underline';

if (! document.getElementById('editorjs-tool-styles')) {
    const style = document.createElement('style');
    style.id = 'editorjs-tool-styles';
    style.textContent = `
        .codex-editor__redactor { padding-bottom: 100px !important; }
        .ce-block { margin-bottom: 4px; }
        .ce-toolbar__plus, .ce-toolbar__settings-btn { color: #9ca3af; }
        .ce-toolbar__plus:hover, .ce-toolbar__settings-btn:hover { color: #374151; background: #f3f4f6; }
        .ce-inline-toolbar { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .ce-conversion-toolbar { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .cdx-settings-button { border-radius: 4px; }
        .ce-popover { border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .ce-popover-item:hover { background: #f3f4f6; }
        .ce-popover-item__title { font-size: 13px; }
        .cdx-image { border-radius: 8px; overflow: hidden; }
        .cdx-image .cdx-image__picture--filled { background: #f9fafb; }
        .cdx-image__caption { border-bottom: 1px solid #e5e7eb; }
        .cdx-image__caption[contenteditable]:empty::before { content: attr(data-placeholder); color: #9ca3af; }
        .ce-header { margin: 0; }
        .ce-code { border-radius: 8px; border: 1px solid #e5e7eb; background: #1e293b; color: #f1f5f9; padding: 12px 16px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.5; }
        .ce-code textarea { color: #f1f5f9; background: transparent; border: none; width: 100%; font-family: inherit; font-size: inherit; line-height: inherit; resize: none; }
        .ce-rawmode { background: #f9fafb; border-radius: 8px; }
        .cdx-table { border-collapse: collapse; width: 100%; }
        .cdx-table td, .cdx-table th { border: 1px solid #e5e7eb; padding: 8px 12px; }
        .cdx-table th { background: #f9fafb; font-weight: 600; }
        .ce-delimiter { display: flex; align-items: center; justify-content: center; padding: 16px 0; }
        .ce-delimiter::before { content: ''; width: 60%; height: 1px; background: #d1d5db; }
        .ce-quote { border-left: 3px solid #6b7280; padding-left: 16px; }
        .cdx-warning { border-left: 3px solid #f59e0b; background: #fffbeb; padding: 12px 16px; border-radius: 0 8px 8px 0; }
        .cdx-checklist { list-style: none; padding: 0; }
        .cdx-checklist .cdx-checklist__item { display: flex; align-items: flex-start; gap: 8px; padding: 4px 0; }
        .cdx-checklist__checkbox { width: 18px; height: 18px; border: 2px solid #d1d5db; border-radius: 4px; cursor: pointer; flex-shrink: 0; margin-top: 2px; }
        .cdx-checklist__checkbox:checked { background: #10b981; border-color: #10b981; }
        .cdx-link { color: #2563eb; }
        .link-tool { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .link-tool__link-info { display: flex; align-items: center; padding: 10px 14px; gap: 10px; }
        .link-tool__title { font-size: 13px; font-weight: 600; color: #111827; }
        .link-tool__description { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .link-tool__domain { font-size: 11px; color: #2563eb; margin-top: 4px; }
        .cdx-attaches { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 16px; }
        .cdx-attaches .cdx-attaches__file-name { font-weight: 600; color: #111827; }
        .cdx-attaches .cdx-attaches__file-size { font-size: 12px; color: #6b7280; }
        .cdx-embed { border-radius: 8px; overflow: hidden; }
        .cdx-embed__caption { border-top: 1px solid #e5e7eb; }
        .ce-marker--active { background: #fef08a; }
        .cdx-inline-code { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 6px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 0.9em; }
    `;
    document.head.appendChild(style);
}

const INLINE_TOOLBAR = ['bold', 'italic', 'marker', 'underline', 'inlineCode', 'link'];

window.createChallengeEditor = function ({ holder, data, placeholder, onChange, wire }) {
    return new EditorJS({
        holder: holder,
        data: data,
        placeholder: placeholder || 'Write challenge content here...',
        tools: {
            paragraph: {
                class: Paragraph,
                inlineToolbar: INLINE_TOOLBAR,
            },
            header: {
                class: Header,
                inlineToolbar: ['marker', 'underline', 'inlineCode', 'link'],
                config: {
                    placeholder: 'Enter a heading',
                    levels: [2, 3, 4],
                    defaultLevel: 2,
                },
            },
            list: {
                class: NestedList,
                inlineToolbar: true,
            },
            checklist: {
                class: Checklist,
                inlineToolbar: true,
            },
            image: {
                class: Image,
                inlineToolbar: true,
                config: {
                    endpoints: {
                        byFile: '/admin/editorjs/image',
                        byUrl: '/admin/editorjs/image-by-url',
                    },
                    field: 'image',
                    types: 'image/png,image/jpeg,image/gif,image/webp,image/svg+xml',
                },
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
            },
            warning: {
                class: Warning,
                inlineToolbar: true,
            },
            delimiter: {
                class: Delimiter,
            },
            table: {
                class: Table,
                inlineToolbar: true,
            },
            code: {
                class: Code,
            },
            raw: {
                class: Raw,
            },
            link: {
                class: Link,
            },
            attaches: {
                class: Attaches,
                config: {
                    endpoint: '/admin/editorjs/attaches',
                    field: 'file',
                },
            },
            embed: {
                class: Embed,
                inlineToolbar: false,
                config: {
                    services: {
                        youtube: true,
                        vimeo: true,
                        twitter: true,
                        instagram: true,
                        twitch: true,
                        lichess: true,
                        codepen: true,
                        gfycat: true,
                    },
                },
            },
            marker: {
                class: Marker,
            },
            inlineCode: {
                class: InlineCode,
            },
            underline: {
                class: Underline,
            },
        },
        onChange: onChange,
    });
};
