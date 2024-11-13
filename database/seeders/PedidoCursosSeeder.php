<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PedidoCursos;
use App\Models\PlanEstudio;
use App\Models\Curso;

class PedidoCursosSeeder extends Seeder
{
    public function run()
    {
        // Crear un PedidoCursos usando datos de ejemplo existentes
        $pedido = PedidoCursos::create([
            'estado' => 'No Enviado',
            'observaciones' => 'Pedido de cursos para el semestre actual',
            'enviado' => false,
            'semestre_id' => 18,       // Asegúrate de que el ID corresponde a un semestre existente
            'facultad_id' => 11,       // Asegúrate de que el ID corresponde a una facultad existente
            'especialidad_id' => 23,    // Asegúrate de que el ID corresponde a una especialidad existente
            'plan_estudio_id' => 1     // Asegúrate de que el ID corresponde a un plan de estudio existente
        ]);

        // Obtener el plan de estudio relacionado con el pedido
        $planEstudio = PlanEstudio::find(1); // Asegúrate de que el ID corresponde al plan de estudio correcto

        // Asociar los cursos obligatorios (nivel diferente de "E") al pedido
        $cursosObligatorios = $planEstudio->cursos()->wherePivot('nivel', '!=', 'E')->get();
        foreach ($cursosObligatorios as $curso) {
            $pedido->cursosObligatorios()->attach($curso->id);
        }

        // Asociar un curso electivo al pedido (nivel "E") si deseas incluirlo
        $cursoElectivo = $planEstudio->cursos()->wherePivot('nivel', 'E')->first();
        if ($cursoElectivo) {
            $pedido->cursosElectivosSeleccionados()->attach($cursoElectivo->id);
        }
    }
}
