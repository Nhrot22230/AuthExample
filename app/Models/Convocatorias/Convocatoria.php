<?php

namespace App\Models\Convocatorias;

use App\Models\Universidad\Seccion;
use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Convocatoria extends Model
{
    use HasFactory;

    protected $table = 'convocatoria';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fechaEntrevista',
        'fechaInicio',
        'fechaFin',
        'estado',
        'seccion_id',
    ];

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(Seccion::class);
    }

    public function gruposCriterios(): BelongsToMany
    {
        return $this->belongsToMany(GrupoCriterios::class, 'grupo_criterios_convocatoria');
    }

    public function comite(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class, 'docente_convocatoria');
    }

    public function candidatos(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'candidato_convocatoria', 'convocatoria_id', 'candidato_id');
    }
}
