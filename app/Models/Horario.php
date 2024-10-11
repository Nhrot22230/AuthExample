<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'curso_id',
        'semestre_id',
        'nombre',
        'codigo',
        'vacantes',
    ];

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public function jefesPractica()
    {
        return $this->belongsToMany(Usuario::class, 'jp_horario')->withTimestamps();
    }
}
