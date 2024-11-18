<?php

namespace App\Models\Tramites;

use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TemaDeTesis extends Model
{
    use HasFactory;

    protected $table = 'tema_de_tesis'; // Nombre correcto de la tabla en la base de datos

    protected $fillable = [
        'titulo',
        'resumen',
        'file_id',
        'file_firmado_id',
        'estado',
        'estado_jurado',
        'fecha_enviado',
        'especialidad_id',
        'comentarios',
        'area_id'
    ];

    // Relación con estudiantes (muchos a muchos)
    public function estudiantes() : BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_tema_tesis', 'tema_tesis_id', 'estudiante_id');
    }

    // Relación con docentes como asesores (muchos a muchos)
    public function asesores(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'asesor_tema_tesis', 'tema_tesis_id', 'docente_id');
    }

    // Relación con docentes como jurados (muchos a muchos)
    public function jurados(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'jurado_tema_tesis', 'tema_tesis_id', 'docente_id');
    }

    // Relación con Especialidad (uno a muchos)
    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    // Relación con Observaciones (uno a muchos)
    public function observaciones(): HasMany
    {
        return $this->hasMany(Observacion::class);
    }

    public function procesoAprobacion() : hasOne
    {
        return $this->hasOne(ProcesoAprobacionTema::class, 'tema_tesis_id');
    }
}
