<?php

namespace App\Models\Delegados;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuarios\Estudiante;
use App\Models\Matricula\Horario;
class Delegado extends Model
{
    use HasFactory;

    // Especificar la tabla asociada si el nombre no sigue la convención de Laravel
    protected $table = 'delegados'; 

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'estudiante_id',
        'horario_id',
    ];

    /**
     * Relaciones
     */

    // Relación con el modelo Alumno
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    // Relación con el modelo Horario
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
    
}
