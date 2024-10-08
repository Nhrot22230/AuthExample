<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    /** @use HasFactory<\Database\Factories\EspecialidadFactory> */
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'facultad_id',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }
}
