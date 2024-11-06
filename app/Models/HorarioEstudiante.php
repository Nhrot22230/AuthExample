<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioEstudiante extends Model
{
    /** @use HasFactory<\Database\Factories\HorarioEstudianteFactory> */
    use HasFactory;

    protected $table = 'estudiante_horario';
    
    protected $fillable = [
        'estudiante_id',
        'horario_id',
        'encuestaDocente',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function horarioEstudianteJps()
    {
        return $this->hasMany(HorarioEstudianteJp::class, 'estudiante_horario_id', 'id');
    }
}
