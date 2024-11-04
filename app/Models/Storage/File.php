<?php

namespace App\Models\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'file_type', 
        'mime_type', 
        'size', 
        'path', 
        'url'
    ];
    
    protected $casts = [
        'size' => 'integer',
    ];
}
