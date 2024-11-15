<?php

namespace App\Models\Convocatorias;

use App\Models\Usuarios\Docente;
use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComiteCandidatoConvocatoria extends Model
{
    use HasFactory;

    protected $table = 'comite_candidato_convocatoria';

    protected $fillable = [
        'docente_id',
        'candidato_id',
        'convocatoria_id',
        'estado'
    ];

    public function miembroComite(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'candidato_id');
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id');
    }
}
