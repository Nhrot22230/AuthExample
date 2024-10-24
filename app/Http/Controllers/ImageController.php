<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('image');
        $path = $file->store('images', 's3');

        Storage::disk('s3')->setVisibility($path, 'public');

        $url = Storage::disk('s3')->url($path);

        return response()->json(['url' => $url], 201);
    }

    public function get($filename)
    {
        $path = 'images/' . $filename;

        if (Storage::disk('s3')->exists($path)) {
            $url = Storage::disk('s3')->url($path);
            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'Image not found'], 404);
    }
}

