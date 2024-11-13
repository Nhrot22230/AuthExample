<?php

namespace App\Models\Solicitudes;

use App\Models\Matricula\Horario;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartaPresentacion extends Model
{
    use HasFactory;

    protected $table = 'carta_presentacion';

    protected $fillable = [
        'estudiante_id',
        'horario_id',
        'motivo',
        'observacion',
        'archivo_pdf',
        'estado'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
}
