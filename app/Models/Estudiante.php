<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    /** @use HasFactory<\Database\Factories\EstudianteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoEstudiante',
        'especialidad_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}
