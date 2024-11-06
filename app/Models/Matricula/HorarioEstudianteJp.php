<?php

namespace App\Models\Matricula;

use App\Models\Usuarios\JefePractica;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioEstudianteJp extends Model
{
    //
    protected $table = 'estudiante_horario_jp';
    use HasFactory;

    protected $fillable = [
        'horario_estudiante_id',
        'usuario_id',
        'encuestaJP',
    ];

    public function horarioEstudiante()
    {
        return $this->belongsTo(HorarioEstudiante::class, 'estudiante_horario_id', 'id');
    }

    public function jefePractica()
    {
        return $this->belongsTo(JefePractica::class);
    }
}
