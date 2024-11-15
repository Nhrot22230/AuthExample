<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Storage\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    /**
     * Upload a file to storage and save its record in the database.
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'file_type' => ['required', Rule::in(['image', 'video', 'audio', 'document'])],
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mkv,mp3,wav,pdf,doc,docx|max:2048', // tamaño máximo en KB
            ]);

            $file = $request->file('file');
            $path = 'files/' . $request->file_type . '/' . $request->name . '/' . uniqid() . '_' . $file->getClientOriginalName();

            Storage::disk('s3')->put($path, file_get_contents($file));
            // $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;

            $fileRecord = File::create([
                'name' => $request->name,
                'file_type' => $request->file_type,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
                'url' => Storage::url($path),
            ]);

            return response()->json(['url' => $fileRecord->url, 'file' => $fileRecord], 201);

        } catch (\Exception $e) {
            Log::error('File Upload Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download a file by its name.
     */
    public function download($filename)
    {
        $fileRecord = File::where('name', $filename)->first();

        if ($fileRecord && Storage::disk('s3')->exists($fileRecord->path)) {
            $file = Storage::disk('s3')->get($fileRecord->path);
            return response($file, 200)->header('Content-Type', $fileRecord->mime_type);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    public function downloadById($id)
    {
        $fileRecord = File::where('id', $id)->first();

        if ($fileRecord && Storage::disk('s3')->exists($fileRecord->path)) {
            $file = Storage::disk('s3')->get($fileRecord->path);
            return response($file, 200)->header('Content-Type', $fileRecord->mime_type);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    /**
     * Delete a file by its name.
     */
    public function deleteFile($filename)
    {
        $fileRecord = File::where('name', $filename)->first();

        if (!$fileRecord) {
            return response()->json(['error' => 'File not found'], 404);
        }

        try {
            Storage::disk('s3')->delete($fileRecord->path);
            $fileRecord->delete();
            return response()->json(['message' => 'File deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('File Deletion Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a file by its name.
     */
    public function updateFile(Request $request, $id)
    {
        $fileRecord = File::where('id', $id)->first();
        if (!$fileRecord) {
            return response()->json(['message' => 'File not found'], 404);
        }

        try {
            Storage::disk('s3')->delete($fileRecord->path);
            $fileRecord->delete();
            return $this->uploadFile($request);
        } catch (\Exception $e) {
            Log::error('File Update Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating file: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Retrieve file metadata by its name.
     */
    public function getFileMetadata($filename)
    {
        $fileRecord = File::where('name', $filename)->first();

        if ($fileRecord) {
            return response()->json($fileRecord, 200);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
