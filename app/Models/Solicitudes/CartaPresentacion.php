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
        'idEstudiante',
        'idHorario',
        'Motivo',
        'Observacion',
        'ArchivoPDF',
        'Estado'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'idEstudiante');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'idHorario');
    }
}
