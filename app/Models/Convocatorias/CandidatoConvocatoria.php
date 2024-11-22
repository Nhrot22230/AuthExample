<?php

namespace App\Models\Convocatorias;

use App\Models\Usuarios\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatoConvocatoria extends Model
{
    use HasFactory;

    protected $table = 'candidato_convocatoria';

    protected $fillable = [
        'convocatoria_id',
        'candidato_id',
        'estadoFinal',
        'file_id'
    ];

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id');
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'candidato_id');
    }
}
