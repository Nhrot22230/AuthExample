<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        } catch (\Exception $e) {
            Log::channel('usuarios')->error($e->getMessage());
            return response()->json(['message' => 'Error al subir la imagen' . $e->getMessage()], 420);
        }

        $file = $request->file('image');
        $path = 'images/' . $request->name;

        Storage::disk('s3')->put($path, file_get_contents($file));

        $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;
        return response()->json(['url' => $url], 201);
    }

    public function get($filename)
    {
        $path = 'images/' . $filename;

        if (Storage::disk('s3')->exists($path)) {
            $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;
            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'Image not found'], 404);
    }

    public function getMIME($filename)
    {
        $path = 'images/' . $filename;

        if (Storage::disk('s3')->exists($path)) {
            $file = Storage::disk('s3')->get($path);

            return response($file, 200);
        }

        return response()->json(['error' => 'Image not found'], 404);
    }
}
