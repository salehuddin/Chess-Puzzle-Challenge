<?php

namespace App\Livewire;

use App\Models\Challenge;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class ChallengeShow extends Component
{
    public Challenge $challenge;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $videos = [];

    /**
     * @var array<int, string>
     */
    public array $imageGallery = [];

    /**
     * @var array<int, string>
     */
    public array $medalImages = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $contentBlocks = [];

    public ?string $posterImageUrl = null;

    public function mount(Challenge $challenge)
    {
        $this->challenge = $challenge;
        $this->challenge->loadCount('puzzles');
        $this->hydrateDisplayData();
    }

    public function render()
    {
        return view('livewire.challenge-show')->layout('layouts.marketing');
    }

    private function hydrateDisplayData(): void
    {
        $this->posterImageUrl = $this->mediaUrl($this->challenge->poster_image);

        $this->imageGallery = array_values(array_filter(array_map(
            fn (mixed $item): ?string => $this->mediaUrl((string) $item),
            $this->asArray($this->challenge->image_gallery)
        )));

        $this->medalImages = array_values(array_filter(array_map(
            fn (mixed $item): ?string => $this->mediaUrl((string) $item),
            $this->asArray($this->challenge->medal_images)
        )));

        $content = $this->challenge->content_blocks;
        $blocks = [];

        if (is_array($content) && isset($content['blocks']) && is_array($content['blocks'])) {
            $blocks = $content['blocks'];
        } elseif (is_array($content) && Arr::isList($content)) {
            $blocks = $content;
        }

        $this->contentBlocks = $blocks;

        $this->videos = array_values(array_filter(array_map(function (mixed $item): ?array {
            if (! is_array($item)) {
                return null;
            }

            $url = trim((string) ($item['url'] ?? ''));
            $embedUrl = $this->toEmbedUrl($url);

            if ($url === '' || $embedUrl === null) {
                return null;
            }

            return [
                'title' => trim((string) ($item['title'] ?? 'Challenge video')),
                'url' => $url,
                'embed_url' => $embedUrl,
            ];
        }, $this->asArray($this->challenge->videos))));
    }

    /**
     * @return array<int, mixed>
     */
    private function asArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        if (Storage::disk('local')->exists($path)) {
            try {
                return Storage::disk('local')->temporaryUrl($path, Carbon::now()->addMinutes(30));
            } catch (\Throwable) {
                return Storage::disk('local')->url($path);
            }
        }

        if (Storage::exists($path)) {
            return Storage::url($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function toEmbedUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);

        if (str_contains($host, 'youtu.be') && $path !== '') {
            return 'https://www.youtube.com/embed/' . Str::before($path, '/');
        }

        if (str_contains($host, 'youtube.com')) {
            $id = (string) ($query['v'] ?? '');

            if ($id !== '') {
                return 'https://www.youtube.com/embed/' . $id;
            }

            if (str_starts_with($path, 'shorts/')) {
                return 'https://www.youtube.com/embed/' . Str::after($path, 'shorts/');
            }
        }

        if (str_contains($host, 'vimeo.com')) {
            $segments = array_values(array_filter(explode('/', $path)));
            $id = end($segments);

            if (is_string($id) && ctype_digit($id)) {
                return 'https://player.vimeo.com/video/' . $id;
            }
        }

        return null;
    }
}
