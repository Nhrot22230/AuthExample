<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'especialidad_id',
    ];

    public function docentes()
    {
        return $this->hasMany(Docente::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}
