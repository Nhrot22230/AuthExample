<?php

namespace App\Models;

use App\Models\Universidad\Especialidad;
use App\Models\Usuarios\Estudiante;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    // Especificar el nombre de la tabla
    protected $table = 'notificaciones';  // Asegúrate de que el nombre de la tabla esté correcto

    protected $fillable = [
        'estudiante_id',
        'especialidad_id',
        'mensaje',
        'leida',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }
}
