<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\EstudianteRiesgo;
use App\Models\InformeRiesgo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use DateTime;

class EstudianteRiesgoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');
        $especialidadId = request('especialidad_id', null); // Obtener el ID de la especialidad
        $facultadId = request('facultad_id', null); // Obtener el ID de la facultad
    }

    public function listar_por_especialidad($request)
    {
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $request)->get();
        $resultado = [];
        $fechaActual = new DateTime();
        $inicioSemanaActual = (clone $fechaActual)->modify('monday this week');
        $finSemanaActual = (clone $fechaActual)->modify('sunday this week');
        $informe_actual = null;

        foreach ($estudiantesRiesgo as $estudiante)
        {
            $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);
            //Busca el informe de esta semana
            $informes = InformeRiesgo::where('codigo_alumno_riesgo', $estudiante->id)->get();
            foreach ($informes as $i){
                $fechaRegistro = new DateTime($i->fecha);
                if ($fechaRegistro >= $inicioSemanaActual && $fechaRegistro <= $finSemanaActual) {
                    // Están en la misma semana
                    $informe_actual = $i;
                    break;
                }
                $informe_actual = null;
            }
            if($informe_actual == null) continue;

            $resultado[] = [
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
                'Estado' => $informe_actual->estado,
                'Fecha' => $informe_actual->fecha,
                'Desempeño' => $informe_actual->desempenho,
                'Observaciones' => $informe_actual->observaciones,
                'Docente' => $informe_actual->nombre
            ];
        }
        return response()->json($resultado);
    }

    public function carga_alumnos_riesgo(Request $request)
    {
        $data = json_decode($request, true);

    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
