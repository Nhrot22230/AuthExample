<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class ImageController extends Controller
{
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'file_type' => 'required|string|image,video,audio,document',
                'file' => 'required|file',
            ]);

            $file = $request->file('file');
            $path = 'files/' . $request->file_type . '/' . $request->name . '/' . uniqid() . '_' . $file->getClientOriginalName();
            
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;
            $mimeType = $file->getClientMimeType();
            $size = $file->getSize();

            $fileRecord = File::create([
                'name' => $request->name,
                'mime_type' => $mimeType,
                'size' => $size,
                'path' => $path,
                'url' => $url,
            ]);
            return response()->json(['url' => $fileRecord->url], 201);
        } catch (\Exception $e) {
            Log::channel('errors')->error($e->getMessage());
            return response()->json(['message' => 'Error al subir el archivo: ' . $e->getMessage()], 420);
        }
    }

    public function download($filename)
    {
        $fileRecord = File::where('name', $filename)->first();

        if ($fileRecord && Storage::disk('s3')->exists($fileRecord->path)) {
            $file = Storage::disk('s3')->get($fileRecord->path);

            return response($file, 200)->header('Content-Type', $fileRecord->mime_type);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
