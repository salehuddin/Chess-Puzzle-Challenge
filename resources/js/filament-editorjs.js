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
import TextVariantTune from '@editorjs/text-variant-tune';

const INLINE_TOOLBAR = ['bold', 'italic', 'marker', 'underline', 'inlineCode', 'link'];

window.createChallengeEditor = function ({ holder, data, placeholder, onChange }) {
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
            textVariant: {
                class: TextVariantTune,
            },
        },
        tunes: ['textVariant'],
        onChange: onChange,
    });
};
