<?php

namespace App\Models\Matricula;

use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioEstudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante_horario';

    protected $fillable = [
        'estudiante_id',
        'horario_id',
        'encuestaDocente',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function horarioEstudianteJps()
    {
        return $this->hasMany(HorarioEstudianteJp::class, 'estudiante_horario_id', 'id');
    }
}
