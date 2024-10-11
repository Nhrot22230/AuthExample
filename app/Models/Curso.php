<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'especialidad_id',
        'cod_curso',
        'nombre',
        'creditos',
        'estado',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
}
