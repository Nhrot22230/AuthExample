<?php

namespace App\Models\EstudianteRiesgo;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class EstudianteRiesgo extends Model
{
    use HasFactory;

    protected $table = 'estudiante_riesgo'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'codigo_estudiante',
        'codigo_curso',
        'codigo_docente',
        'horario',
        'codigo_especialidad',
        'riesgo',
        'estado',
        'fecha',
        'desempenho',
        'observaciones',
        'nombre',
        'ciclo'
    ];

    // Relaciones
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'codigo_estudiante', 'codigoEstudiante');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'codigo_curso', 'id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'codigo_docente', 'codigoDocente');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'codigo_especialidad', 'id');
    }

    public function informes(): HasMany
    {
        return $this->hasMany(InformeRiesgo::class, 'codigo_alumno_riesgo', 'id');
    }

    public function usuario(): HasOneThrough
    {
        return $this->hasOneThrough(
            Usuario::class,
            Estudiante::class,
            'codigoEstudiante', // Foreign key en Estudiante (intermedio)
            'id',               // Foreign key en Usuario
            'codigo_estudiante', // Local key en EstudianteRiesgo
            'usuario_id'        // Local key en Estudiante
        );
    }
}
