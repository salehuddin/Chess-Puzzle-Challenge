<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorJsUploadController extends Controller
{
    public function storeImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $file = $request->file('image');
        $directory = 'artworks/challenges/content/'.now()->format('Y/m');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 'public');

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk('public')->url($path),
            ],
        ]);
    }

    public function storeImageUrl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $url = $request->input('url');

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => $url,
            ],
        ]);
    }

    public function storeAttaches(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $directory = 'artworks/challenges/content/'.now()->format('Y/m');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 'public');

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk('public')->url($path),
                'title' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
            ],
        ]);
    }
}
