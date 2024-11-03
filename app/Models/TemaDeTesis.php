<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemaDeTesis extends Model
{
    use HasFactory;

    protected $table = 'tema_de_tesis'; // Nombre correcto de la tabla en la base de datos

    protected $fillable = [
        'titulo',
        'resumen',
        'documento',
        'estado', 
        'estado_jurado',
        'fecha_enviado',
        'especialidad_id',
        'comentarios',
    ];

    // Relación con estudiantes (muchos a muchos)
    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_tema_tesis', 'tema_tesis_id', 'estudiante_id');
    }

    // Relación con docentes como asesores (muchos a muchos)
    public function asesores()
    {
        return $this->belongsToMany(Docente::class, 'asesor_tema_tesis', 'tema_tesis_id', 'docente_id');
    }

    // Relación con docentes como jurados (muchos a muchos)
    public function jurados()
    {
        return $this->belongsToMany(Docente::class, 'jurado_tema_tesis', 'tema_tesis_id', 'docente_id');
    }

    // Relación con Especialidad (uno a muchos)
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    // Relación con Observaciones (uno a muchos)
    public function observaciones()
    {
        return $this->hasMany(Observacion::class);
    }
}
