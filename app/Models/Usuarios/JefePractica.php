<?php

namespace App\Models\Usuarios;

use App\Models\Matricula\Horario;
use App\Models\Matricula\HorarioEstudianteJp;
use Illuminate\Database\Eloquent\Model;

class JefePractica extends Model
{
    protected $table = 'jp_horario';

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

    public function horarioEstudianteJps()
    {
        return $this->hasMany(HorarioEstudianteJp::class, 'jp_horario_id', 'id');
    }
}
