<?php

namespace App\Models\Tramites;

use \Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Usuarios\Usuario;
use App\Models\Storage\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoAprobacionTema extends Model
{
    use HasFactory;

    protected $table = 'estado_aprobacion_tema';

    protected $fillable = [
        'proceso_aprobacion_id',
        'usuario_id',
        'estado',
        'fecha_decision',
        'comentarios',
        'pdf_url',
        'file_id',
        'responsable'
    ];

    public function procesoAprobacion() : BelongsTo
    {
        return $this->belongsTo(ProcesoAprobacionTema::class, 'proceso_aprobacion_id');
    }

    public function usuario() : BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function file(): HasOne {
        return $this->hasOne(File::class, 'id', 'file_id');
    }
}
