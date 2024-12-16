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
        'estudiante_horario_id',
        'jp_horario_id',
        'encuestaJP',
    ];

    public function horarioEstudiante()
    {
        return $this->belongsTo(HorarioEstudiante::class, 'estudiante_horario_id', 'id');
    }

    public function jefePractica()
    {
        return $this->belongsTo(JefePractica::class, 'usuario_id');
    }
}
