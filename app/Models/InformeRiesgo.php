<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeRiesgo extends Model
{
    use HasFactory;

    protected $table = 'informes_riesgo'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'codigo_alumno_riesgo',
        'fecha',
        'desempenho',
        'observaciones',
        'estado',
        'nombre_profesor'
    ];

    public function alumno_riesgo()
    {
        return $this->belongsTo(EstudianteRiesgo::class, 'codigo_alumno_riesgo', 'id');
    }
}
