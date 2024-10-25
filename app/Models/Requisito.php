<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    /** @use HasFactory<\Database\Factories\RequisitoFactory> */
    use HasFactory;

    protected $fillable = [
        'nivel',
        'curso_id',
        'curso_requisito_id',
        'plan_estudio_id',
        'tipo',
        'notaMinima',
        'cantCreditos',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function cursoRequisito()
    {
        return $this->belongsTo(Curso::class);
    }
}
