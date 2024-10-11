<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    use HasFactory;

    protected $fillable = [
        'anho',
        'periodo',
        'estado',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
}
