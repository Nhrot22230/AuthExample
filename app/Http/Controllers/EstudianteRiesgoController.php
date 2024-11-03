<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\EstudianteRiesgo;
use App\Models\InformeRiesgo;
use App\Models\Semestre;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use DateTime;
use Nette\Schema\ValidationException;
use function PHPUnit\Framework\isEmpty;

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

    public function listar_por_especialidad_director(Request $request)
    {
        $request = $request->IdEspecialidad;
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $request)->get();
        $resultado = [];
        $fechaActual = new DateTime();
        $ciclo = Semestre::where('estado', 'activo')->first();
        $periodo = $ciclo->anho . "-" . $ciclo->periodo;
        foreach ($estudiantesRiesgo as $estudiante)
        {
            $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);

            if($periodo != $estudiante->ciclo) continue;

            $resultado[] = [
                'Id' => $estudiante->id,
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
            ];
        }
        return response()->json($resultado);
    }

    public function listar_informes_estudiante(Request $request)
    {
        $request = $request->CodigoAlumnoRiesgo;
        $informes = InformeRiesgo::where('codigo_alumno_riesgo', $request)->get();
        $resultado = [];
        foreach($informes as $i){
            $resultado[] = [
                'Estado' => $i->estado,
                'Fecha' => $i->fecha,
                'Desempeño' => $i->desempenho,
                'Observaciones' => $i->observaciones,
                'Docente' => $i->nombre
            ];
        }
        return response()->json($resultado);
    }

    public function agregar_informe_estudiante(Request $request)
    {
        $numero_semana = $request->NumeroSemana;
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = new DateTime($ciclo->fecha_inicio);
        $fechaInicio->modify("+{$numero_semana} weeks");
        InformeRiesgo::create([
            'estado' => 'Pendiente',
            'fecha' => $fechaInicio,
            'codigo_alumno_riesgo' => $request->IdAlumnoRiesgo,
        ]);
    }

    public function actualizar_informe_estudiante(Request $request)
    {
        $informe = InformeRiesgo::find($request->IdInforme);
        $informe->desempenho = $request->Desempeño;
        $informe->observaciones = $request->Observaciones;
        $informe->estado = 'Respondida';
        $informe->nombre_profesor = $request->NombreProfesor;
        $informe->save();
    }
/*
    public function listar_por_especialidad_profesor(Request $request) //Para el profesor
    {
        //$data = json_decode($request, true);
        $profesor = $request->CodigoProfesor;//$data['CodigoProfesor'];
        $request = $request->Especialidad;
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $request)->where('codigo_docente', $profesor)->get();
        if($estudiantesRiesgo->isEmpty()) return response()->json("");
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
                'Id' => $estudiante->id,
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
                'IdInforme' => $informe_actual->id,
                'Estado' => $informe_actual->estado,
                'Fecha' => $informe_actual->fecha,
                'Desempeño' => $informe_actual->desempenho,
                'Observaciones' => $informe_actual->observaciones,
                'Docente' => $informe_actual->nombre_profesor
            ];
        }
        return response()->json($resultado);
    }*/

    public function listar_por_especialidad_profesor(Request $request) //Para el profesor
    {
        //$data = json_decode($request, true);
        $profesor = $request->CodigoProfesor;
        $especialidad = $request->Especialidad;
        $estado = $request->Estado ?? 'Todos'; // "Todos", "Pendientes" o "Respondida"
        $riesgo = $request->Riesgo ?? 'Todos'; // "Tercera", "Cuarta" u "Otros"
        $busqueda = $request->Busqueda ?? ''; // Búsqueda por código o nombre

        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidad)
            ->where('codigo_docente', $profesor);

// Aplicar filtro de estado
        if ($estado !== 'Todos') {
            $estudiantesRiesgo->whereHas('informes', function ($query) use ($estado) {
                $query->where('estado', $estado);
            });
        }

// Aplicar filtro de riesgo
        if ($riesgo !== 'Todos') {
            $estudiantesRiesgo->where('riesgo', $riesgo);
        }

// Filtrar por búsqueda de código o nombre
        if (!empty($busqueda)) {
            $estudiantesRiesgo->where(function ($query) use ($busqueda) {
                $query->where('codigo_estudiante', 'like', '%' . $busqueda . '%')
                    ->orWhereHas('usuario', function ($query) use ($busqueda) {
                        $query->where('nombre', 'like', '%' . $busqueda . '%');
                    });
            });
        }

        $estudiantesRiesgo = $estudiantesRiesgo->get();

        if ($estudiantesRiesgo->isEmpty()) {
            return response()->json("");
        }

        $resultado = [];
        $fechaActual = new DateTime();
        $inicioSemanaActual = (clone $fechaActual)->modify('monday this week');
        $finSemanaActual = (clone $fechaActual)->modify('sunday this week');

        foreach ($estudiantesRiesgo as $estudiante) {
            $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);
            $informes = InformeRiesgo::where('codigo_alumno_riesgo', $estudiante->id)->get();

            $informe_actual = null;
            foreach ($informes as $informe) {
                $fechaRegistro = new DateTime($informe->fecha);
                if ($fechaRegistro >= $inicioSemanaActual && $fechaRegistro <= $finSemanaActual) {
                    $informe_actual = $informe;
                    break;
                }
            }

            if ($informe_actual == null) continue;

            $resultado[] = [
                'Id' => $estudiante->id,
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
                'IdInforme' => $informe_actual->id,
                'Estado' => $informe_actual->estado,
                'Fecha' => $informe_actual->fecha,
                'Desempeño' => $informe_actual->desempenho,
                'Observaciones' => $informe_actual->observaciones,
                'Docente' => $informe_actual->nombre_profesor
            ];
        }

        return response()->json($resultado);
    }

    public function carga_alumnos_riesgo(Request $request)
    {
        try{
            $request->validate([
                'alumnos'=> 'required|array',
                'Especialidad' => 'required',
                'alumnos.*.Codigo' => 'required',
                'alumnos.*.CodigoCurso' => 'required|exists:cursos,cod_curso',
                'alumnos.*.Horario' => 'required',
                'alumnos.*.Riesgo' => 'required',
                'alumnos.*.Fecha' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del plan de estudio', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $ciclo = Semestre::where('estado', 'activo')->first();
        foreach ($request->alumnos as $alumno){
            Log::channel('usuarios')->info($alumno);
            EstudianteRiesgo::create([
                'codigo_estudiante' => $alumno['Codigo'],
                'codigo_curso' => Curso::where('cod_curso', $alumno['CodigoCurso'])->first()->id,
                'codigo_especialidad' => $request->Especialidad,
                'horario' => $alumno['Horario'],
                'riesgo' => $alumno['Riesgo'],
                'fecha' => $alumno['Fecha'],
                'ciclo' => $ciclo->anho . "-" . $ciclo->periodo,
            ]);
        }
        return response()->json("",201);
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
