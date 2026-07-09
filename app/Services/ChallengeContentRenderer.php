<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Arr;

class ChallengeContentRenderer
{
    private ?HTMLPurifier $textPurifier = null;

    private ?HTMLPurifier $rawPurifier = null;

    /**
     * Render Editor.js content_blocks JSON to styled HTML.
     *
     * @param  mixed  $content  The raw content_blocks value (JSON string or array).
     * @return string Sanitized, styled HTML ready for `{!! !!}` output.
     */
    public function render(mixed $content): string
    {
        $blocks = $this->parseBlocks($content);

        if (empty($blocks)) {
            return '';
        }

        $html = '';

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $html .= $this->renderBlock($type, $data);
        }

        return $html;
    }

    /**
     * Parse the raw content_blocks value into an array of blocks.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parseBlocks(mixed $content): array
    {
        if (empty($content)) {
            return [];
        }

        if (is_string($content)) {
            try {
                $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return [];
            }
        }

        if (! is_array($content)) {
            return [];
        }

        if (isset($content['blocks']) && is_array($content['blocks'])) {
            return $content['blocks'];
        }

        if (Arr::isList($content)) {
            return $content;
        }

        return [];
    }

    /**
     * Render a single block by dispatching to the appropriate Blade partial.
     */
    private function renderBlock(string $type, array $data): string
    {
        $data = $this->sanitizeBlockData($type, $data);

        $view = "components.editorjs-blocks.{$type}";

        if (! view()->exists($view)) {
            return '';
        }

        return view($view, ['data' => $data])->render();
    }

    /**
     * Sanitize the data fields of a block based on its type.
     */
    private function sanitizeBlockData(string $type, array $data): array
    {
        return match ($type) {
            'paragraph' => [
                'text' => $this->purifyText($data['text'] ?? ''),
            ],
            'header' => [
                'text' => $this->purifyText($data['text'] ?? ''),
                'level' => $this->clampInt($data['level'] ?? 2, 1, 6),
            ],
            'list' => $this->sanitizeListData($data),
            'checklist' => [
                'items' => array_map(fn ($item) => [
                    'text' => $this->purifyText(is_array($item) ? ($item['text'] ?? '') : (string) $item),
                    'checked' => is_array($item) ? (bool) ($item['checked'] ?? false) : false,
                ], $data['items'] ?? []),
            ],
            'image' => [
                'file' => [
                    'url' => $this->sanitizeUrl($data['file']['url'] ?? ''),
                ],
                'caption' => $this->purifyText($data['caption'] ?? ''),
                'withBorder' => (bool) ($data['withBorder'] ?? false),
                'stretched' => (bool) ($data['stretched'] ?? false),
                'withBackground' => (bool) ($data['withBackground'] ?? false),
            ],
            'quote' => [
                'text' => $this->purifyText($data['text'] ?? ''),
                'caption' => $this->purifyText($data['caption'] ?? ''),
                'alignment' => in_array($data['alignment'] ?? 'left', ['left', 'center'], true)
                    ? $data['alignment']
                    : 'left',
            ],
            'warning' => [
                'title' => $this->purifyText($data['title'] ?? ''),
                'message' => $this->purifyText($data['message'] ?? ''),
            ],
            'delimiter' => [],
            'table' => [
                'content' => array_map(
                    fn ($row) => array_map(fn ($cell) => $this->purifyText((string) $cell), $row),
                    $data['content'] ?? [],
                ),
            ],
            'code' => [
                'code' => e($data['code'] ?? ''),
            ],
            'raw' => [
                'html' => $this->purifyRaw($data['html'] ?? ''),
            ],
            'link' => [
                'link' => $this->sanitizeUrl($data['link'] ?? ''),
                'meta' => [
                    'title' => $this->purifyText($data['meta']['title'] ?? ''),
                    'description' => $this->purifyText($data['meta']['description'] ?? ''),
                    'image' => [
                        'url' => $this->sanitizeUrl($data['meta']['image']['url'] ?? ''),
                    ],
                ],
            ],
            'attaches' => [
                'file' => [
                    'url' => $this->sanitizeUrl($data['file']['url'] ?? ''),
                    'title' => e($data['file']['title'] ?? ''),
                    'size' => (int) ($data['file']['size'] ?? 0),
                    'extension' => e($data['file']['extension'] ?? ''),
                ],
                'title' => e($data['title'] ?? ''),
            ],
            'embed' => [
                'service' => e($data['service'] ?? ''),
                'source' => $this->sanitizeUrl($data['source'] ?? ''),
                'embed' => $this->sanitizeUrl($data['embed'] ?? ''),
                'width' => (int) ($data['width'] ?? 0),
                'height' => (int) ($data['height'] ?? 0),
                'caption' => $this->purifyText($data['caption'] ?? ''),
            ],
            default => [],
        };
    }

    /**
     * Sanitize list block data, handling both old (string items) and new (nested object items) formats.
     */
    private function sanitizeListData(array $data): array
    {
        $items = $data['items'] ?? [];
        $style = ($data['style'] ?? 'unordered') === 'ordered' ? 'ordered' : 'unordered';

        return [
            'items' => array_map(fn ($item) => $this->sanitizeListItem($item), $items),
            'style' => $style,
        ];
    }

    /**
     * Sanitize a single list item (string or {content, items} object).
     */
    private function sanitizeListItem(mixed $item): array
    {
        if (is_string($item)) {
            return [
                'content' => $this->purifyText($item),
                'items' => [],
            ];
        }

        if (! is_array($item)) {
            return [
                'content' => '',
                'items' => [],
            ];
        }

        return [
            'content' => $this->purifyText($item['content'] ?? ''),
            'items' => array_map(fn ($subItem) => $this->sanitizeListItem($subItem), $item['items'] ?? []),
        ];
    }

    /**
     * Purify inline HTML text using HTMLPurifier with a safe set of allowed tags.
     */
    private function purifyText(string $html): string
    {
        return $this->getTextPurifier()->purify($html);
    }

    /**
     * Purify raw HTML blocks with a broader (but still safe) set of allowed tags.
     */
    private function purifyRaw(string $html): string
    {
        return $this->getRawPurifier()->purify($html);
    }

    /**
     * Get the text purifier (for paragraph/header/quote/caption text fields).
     * Allows: bold, italic, underline, links, mark, inline code.
     */
    private function getTextPurifier(): HTMLPurifier
    {
        if ($this->textPurifier !== null) {
            return $this->textPurifier;
        }

        $config = $this->createBasePurifierConfig();
        $config->set('HTML.Allowed', 'b,i,u,a[href|target],mark,code,br,strong,em');
        $config->set('HTML.DefinitionID', 'challenge-text-purifier');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
        }

        return $this->textPurifier = new HTMLPurifier($config);
    }

    /**
     * Get the raw HTML purifier (for the raw block type).
     * Allows a broad set of structural/content tags but strips scripts, event handlers, etc.
     */
    private function getRawPurifier(): HTMLPurifier
    {
        if ($this->rawPurifier !== null) {
            return $this->rawPurifier;
        }

        $config = $this->createBasePurifierConfig();
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'hr',
            'div', 'span',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'b', 'em', 'i', 'u', 's', 'del', 'mark', 'code', 'pre', 'blockquote', 'sub', 'sup',
            'a[href|target|title]',
            'img[src|alt|width|height]',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'iframe[src|width|height|frameborder|allowfullscreen]',
            'video[src|width|height|controls]',
            'source[src|type]',
            'details', 'summary',
        ]));
        $config->set('HTML.DefinitionID', 'challenge-raw-purifier');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
        }

        return $this->rawPurifier = new HTMLPurifier($config);
    }

    /**
     * Create a base HTMLPurifier config with sensible defaults.
     */
    private function createBasePurifierConfig(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.TargetBlank', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('AutoFormat.RemoveEmpty', true);

        $cacheDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'purifier';
        if (! is_dir($cacheDirectory)) {
            @mkdir($cacheDirectory, 0777, true);
        }
        $config->set('Cache.SerializerPath', $cacheDirectory);

        return $config;
    }

    /**
     * Sanitize a URL — only allow http/https/mailto/tel schemes.
     */
    private function sanitizeUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        $parsed = parse_url($url);

        $scheme = strtolower($parsed['scheme'] ?? '');

        if ($scheme !== '' && ! in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
            return '';
        }

        return e($url);
    }

    /**
     * Clamp an integer value between min and max.
     */
    private function clampInt(mixed $value, int $min, int $max): int
    {
        $value = (int) $value;

        return max($min, min($max, $value));
    }
}
