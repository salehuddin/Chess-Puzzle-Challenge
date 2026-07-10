<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Finder\Finder;

class DocumentationService
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = base_path('docs');
    }

    /**
     * @return Collection<int, array{name: string, label: string, documents: Collection<int, array{path: string, title: string, description: string, updated_at: Carbon}>}>
     */
    public function categories(): Collection
    {
        if (! is_dir($this->basePath)) {
            return collect();
        }

        $featuresPath = $this->basePath.'/features';

        if (! is_dir($featuresPath)) {
            return collect();
        }

        return collect(File::directories($featuresPath))
            ->sort()
            ->map(fn (string $path) => $this->makeCategory($path))
            ->filter(fn (array $category) => $category['documents']->isNotEmpty());
    }

    /**
     * @return array{name: string, label: string, documents: Collection<int, array{path: string, title: string, description: string, updated_at: Carbon}>}
     */
    protected function makeCategory(string $path): array
    {
        $name = basename($path);

        return [
            'name' => $name,
            'label' => $this->categoryLabel($name),
            'documents' => $this->documentsInCategory($path),
        ];
    }

    protected function categoryLabel(string $name): string
    {
        return Str::headline(str_replace(['-', '_'], ' ', $name));
    }

    /**
     * @return Collection<int, array{path: string, title: string, description: string, updated_at: Carbon}>
     */
    protected function documentsInCategory(string $path): Collection
    {
        if (! is_dir($path)) {
            return collect();
        }

        $documents = collect();

        $finder = new Finder;
        $finder->files()->in($path)->name('*.md')->sortByName();

        foreach ($finder as $file) {
            $documents->push($this->parseDocument($file->getRealPath()));
        }

        return $documents;
    }

    /**
     * @return array{path: string, title: string, description: string, updated_at: Carbon}
     */
    public function parseDocument(string $filePath): array
    {
        $relativePath = $this->normalizePath(str_replace($this->normalizePath($this->basePath).'/', '', $this->normalizePath($filePath)));

        $content = File::get($filePath);

        return [
            'path' => $relativePath,
            'title' => $this->extractTitle($content),
            'description' => $this->extractDescription($content),
            'updated_at' => now()->setTimestamp(filemtime($filePath)),
        ];
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function extractTitle(string $content): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Untitled';
    }

    protected function extractDescription(string $content): string
    {
        // Remove the title if it exists.
        $withoutTitle = preg_replace('/^#\s+.+\n+/m', '', $content, 1);

        // Find the first non-empty line that is not a heading or list item.
        $lines = explode("\n", (string) $withoutTitle);

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^(#{1,6}\s|[-*+]\s|\d+\.\s|```|\[|\|)/', $trimmed)) {
                continue;
            }

            return $trimmed;
        }

        return '';
    }

    public function renderMarkdown(string $content): string
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($content);
    }

    public function findDocument(string $path): ?array
    {
        $filePath = $this->normalizePath($this->basePath.'/'.ltrim($path, '/'));

        if (! File::exists($filePath) || ! Str::endsWith($filePath, '.md')) {
            return null;
        }

        $document = $this->parseDocument($filePath);
        $document['content'] = $this->renderMarkdown(File::get($filePath));

        return $document;
    }

    public function defaultDocument(): ?array
    {
        $categories = $this->categories();

        if ($categories->isEmpty()) {
            return null;
        }

        $firstCategory = $categories->first();
        $firstDocument = $firstCategory['documents']->first();

        if ($firstDocument === null) {
            return null;
        }

        return $this->findDocument($firstDocument['path']);
    }
}
