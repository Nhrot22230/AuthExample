<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriculaAdicional extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id', 
        'especialidad_id', 
        'motivo', 
        'justificacion', 
        'estado', 
        'motivo_rechazo'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }
}
