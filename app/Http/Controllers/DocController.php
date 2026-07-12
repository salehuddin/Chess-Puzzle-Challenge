<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\View\View;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocController extends Controller
{
    private const ROOT_DOCS = [
        'AGENTS.md' => 'Workflow Guide',
        'DEPLOYMENT.md' => 'Deployment Guide (VPS + Coolify)',
        'DEPLOYMENT-CLOUD-HOSTING.md' => 'Deployment Guide (Cloud Hosting)',
        'ROADMAP.md' => 'Roadmap',
        'CPC-PRD.md' => 'Product Requirements Document',
        'dev-plan.md' => 'Development Plan',
    ];

    public function index(): View
    {
        $featureDocs = $this->collectFeatureDocs();
        $rootDocs = self::ROOT_DOCS;

        return view('docs.index', compact('rootDocs', 'featureDocs'));
    }

    public function show(string $path): View
    {
        $filesystem = app(Filesystem::class);

        if (isset(self::ROOT_DOCS[$path])) {
            $fullPath = base_path($path);
            $title = self::ROOT_DOCS[$path];
        } else {
            $sanitized = str_replace(['..', '\\'], ['', '/'], $path);

            if (! Str::endsWith($sanitized, '.md')) {
                $sanitized .= '.md';
            }

            $fullPath = base_path('docs/'.$sanitized);
            $title = Str::title(str_replace(['-', '/'], [' ', ' / '], str_replace('.md', '', $sanitized)));
        }

        if (! $filesystem->exists($fullPath)) {
            throw new NotFoundHttpException('Documentation page not found.');
        }

        $content = $filesystem->get($fullPath);
        $html = (new GithubFlavoredMarkdownConverter)->convert($content)->getContent();

        return view('docs.show', [
            'html' => $html,
            'title' => $title,
            'path' => $path,
        ]);
    }

    /**
     * @return array<string, array<int, array{path: string, title: string}>>
     */
    private function collectFeatureDocs(): array
    {
        $filesystem = app(Filesystem::class);
        $root = base_path('docs/features');

        if (! $filesystem->exists($root)) {
            return [];
        }

        $categories = [];
        $dirs = $filesystem->directories($root);

        foreach ($dirs as $dir) {
            $categoryName = Str::title(basename($dir));
            $files = $filesystem->files($dir);

            $items = collect($files)
                ->map(fn ($file) => [
                    'path' => 'features/'.basename($dir).'/'.basename($file),
                    'title' => Str::title(str_replace(['-', '.md'], [' ', ''], basename($file))),
                ])
                ->sortBy('title')
                ->values()
                ->all();

            $categories[$categoryName] = $items;
        }

        return $categories;
    }
}
