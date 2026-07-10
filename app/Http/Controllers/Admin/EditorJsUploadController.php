<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditorJsUploadController extends Controller
{
    public function storeImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => ['required', 'image', 'max:10240'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e, 'Image validation failed');
        }

        try {
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
        } catch (\Throwable $e) {
            Log::error('Editor.js image upload failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Could not upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    public function storeImageUrl(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'url' => ['required', 'url'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e, 'Image URL validation failed');
        }

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
        try {
            $request->validate([
                'file' => ['required', 'file', 'max:20480'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e, 'File validation failed');
        }

        try {
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
        } catch (\Throwable $e) {
            Log::error('Editor.js file upload failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => 0,
                'message' => 'Could not upload file: '.$e->getMessage(),
            ], 500);
        }
    }

    private function validationErrorResponse(ValidationException $e, string $context): JsonResponse
    {
        Log::warning($context, [
            'errors' => $e->errors(),
        ]);

        return response()->json([
            'success' => 0,
            'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed',
        ], 422);
    }
}
