<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioEstudianteJp extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'horario_estudiante_id',
        'usuario_id',
        'encuestaJP',
    ];

    public function horarioEstudiante()
    {
        return $this->belongsTo(HorarioEstudiante::class);
    }

    public function jefePractica()
    {
        return $this->belongsTo(JefePractica::class);
    }
}
