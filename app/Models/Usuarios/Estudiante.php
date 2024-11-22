<?php

namespace App\Models\Usuarios;

use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudiante;
use App\Models\Tramites\TemaDeTesis;
use App\Models\Universidad\Especialidad;
use App\Models\Matricula\CartaPresentacionSolicitud;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    /** @use HasFactory<EstudianteFactory> */
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'codigoEstudiante',
        'especialidad_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function horarioEstudiantes(): HasMany
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function horarios()
    {
        return $this->belongsToMany(Horario::class,'estudiante_horario', 'estudiante_id', 'horario_id');
    }

    public function estudiantesRiesgo(): HasMany
    {
        return $this->hasMany(EstudianteRiesgo::class, 'codigo_estudiante', 'codigoEstudiante');
    }

    public function cartasPresentacion(): HasMany
    {
        return $this->hasMany(CartaPresentacionSolicitud::class, 'idEstudiante');
    }

    public function temasDeTesis() : BelongsToMany
    {
        return $this->belongsToMany(TemaDeTesis::class, 'estudiante_tema_tesis', 'estudiante_id', 'tema_tesis_id');
    }

}
