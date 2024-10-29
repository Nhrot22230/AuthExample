<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JefePractica extends Model
{
    protected $fillable = [
        'usuario_id',
        'horario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function horarioEstudianteJPs()
    {
        return $this->hasMany(HorarioEstudianteJp::class);
    }
}
