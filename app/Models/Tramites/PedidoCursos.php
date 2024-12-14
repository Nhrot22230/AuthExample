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
            ->wherePivot('nivel', '!=', '0');
    }

    // Obtener cursos electivos seleccionados específicamente para este pedido
    public function cursosElectivosSeleccionados()
    {
        return $this->belongsToMany(Curso::class, 'pedido_curso_electivo')
                    ->withPivot('nivel', 'creditosReq')
                    ->wherePivot('nivel', '0');
    }

    // Método para obtener todos los cursos del pedido
    public function obtenerCursos()
    {
        $semestreId = $this->semestre_id;
    
        // Incluimos los horarios filtrados por semestre en cursos obligatorios y electivos
        $cursosObligatorios = $this->cursosObligatorios()->with(['horarios' => function ($query) use ($semestreId) {
            $query->where('semestre_id', $semestreId);
        }])->get();
    
        $cursosElectivos = $this->cursosElectivosSeleccionados()->with(['horarios' => function ($query) use ($semestreId) {
            $query->where('semestre_id', $semestreId);
        }])->get();
    
        // Combinar los cursos obligatorios y electivos
        return $cursosObligatorios->merge($cursosElectivos);
    }
}