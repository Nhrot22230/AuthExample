<?php

namespace App\Models\Matricula;

use App\Models\Encuestas\Encuesta;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\JefePractica;
use App\Models\Usuarios\Usuario;
use App\Models\Matricula\HorarioActividad;
use App\Models\Solicitudes\CartaPresentacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Delegados\Delegado;
class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'curso_id',
        'semestre_id',
        'nombre',
        'codigo',
        'vacantes',
        'oculto',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
    }

    public function jefePracticas()
    {
        return $this->hasMany(JefePractica::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_horario');
    }


    public function usuarios(): HasManyThrough
    {
        return $this->hasManyThrough(Usuario::class, JefePractica::class, 'horario_id', 'id', 'id', 'usuario_id');
    }

    public function horarioEstudiantes()
    {
        return $this->hasMany(HorarioEstudiante::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(
            Estudiante::class,        // Modelo relacionado
            'estudiante_horario',     // Nombre de la tabla intermedia
            'horario_id',             // Llave foránea del modelo actual en la tabla intermedia
            'estudiante_id'           // Llave foránea del modelo relacionado en la tabla intermedia
        );
    }

    public function encuestas(): BelongsToMany
    {
        return $this->belongsToMany(Encuesta::class, 'encuesta_horario');
    }

    public function actividades()
    {
        return $this->hasMany(HorarioActividad::class, 'horario_id'); // Relación correcta
    }
    public function cartasPresentacion(): HasMany
    {
        return $this->hasMany(CartaPresentacion::class, 'idHorario');
    }
    public function delegado()
    {
        return $this->hasOne(Delegado::class, 'horario_id', 'id');
    }
    public function jefesPractica()
    {
        return $this->hasMany(JefePractica::class, 'horario_id');
    }
}
