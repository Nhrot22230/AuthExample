<?php

namespace App\Models\Tramites;

use App\Models\Universidad\Curso;
use App\Models\Universidad\Especialidad;
use App\Models\Universidad\Facultad;
use App\Models\Universidad\PlanEstudio;
use App\Models\Universidad\Semestre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCursos extends Model
{
    use HasFactory;

    protected $fillable = [
        'estado',
        'observaciones',
        'enviado',
        'semestre_id',
        'facultad_id',
        'especialidad_id',
        'plan_estudio_id',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public function planEstudio()
    {
        return $this->belongsTo(PlanEstudio::class);
    }

    // Obtener cursos obligatorios (nivel distinto de 'E') para este plan de estudios
    public function cursosObligatorios()
    {
        return $this->planEstudio->cursos()
            ->wherePivot('nivel', '!=', 'E');
    }

    // Obtener cursos electivos seleccionados específicamente para este pedido
    public function cursosElectivosSeleccionados()
    {
        return $this->belongsToMany(Curso::class, 'pedido_curso_electivo')
                    ->withPivot('nivel', 'creditosReq')
                    ->wherePivot('nivel', 'E');
    }

    // Método para obtener todos los cursos del pedido
    public function obtenerCursos()
    {
        return $this->cursosObligatorios->merge($this->cursosElectivosSeleccionados);
    }
}