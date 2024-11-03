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
use Illuminate\Validation\ValidationException;
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
        $ciclo = Semestre::where('estado', 'activo')->first();
        $periodo = $ciclo->anho . "-" . $ciclo->periodo;
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $request)->where('ciclo', $periodo)->get();
        $resultado = [];

        foreach ($estudiantesRiesgo as $estudiante)
        {
            $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);

            $resultado[] = [
                'Id' => $estudiante->id,
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
                'Fecha' => $estudiante->fecha
            ];
        }
        return response()->json($resultado);
    }

    public function listar_informes_director(Request $request)
    {
        try{
            $request->validate([
                'IdEspecialidad'=> 'required',
                'search' => 'required',
                'informes' => 'required',
                'NombreProfesor' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del actualizado de informes', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $idEspecialidad = $request->IdEspecialidad;
        $busqueda = $request->search;
        $n_informes = $request->informes;
        $ciclo = Semestre::where('estado', 'activo')->first();
        $periodo = $ciclo->anho . "-" . $ciclo->periodo;
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $idEspecialidad)->where('ciclo', $periodo);
        if(!empty($busqueda)){
            $estudiantesRiesgo->where(function ($query) use ($busqueda) {
                $query->where('codigo_estudiante', 'like', '%' . $busqueda . '%')
                    ->orWhereHas('usuario', function ($query) use ($busqueda) {
                        $query->where('nombre', 'like', '%' . $busqueda . '%');
                    });
            });
        }
        $estudiantesRiesgo = $estudiantesRiesgo->get();
        $resultado = [];
        foreach($estudiantesRiesgo as $estudiante)
        {
            $cod_alumno = $estudiante->id;
            $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);
            $informes = InformeRiesgo::where('codigo_alumno_riesgo', $cod_alumno);
            if ($n_informes !== 'Todos') {
                $informes->where('semana', intval($n_informes));
            }
            $informes = $informes->get();
            $informes_estudiante = [];
            foreach ($informes as $i)
            {
                if($i->estado == 'Pendiente') continue;
                $resultado[] = [ //Agrega el número de informe con el estudiante al informe
                    'IdInforme' => $i->id,
                    'NumInforme' => $i->semana,
                    'Informe' => $i->desempenho,
                    'FechaInforme' => $i->fecha,
                    'Docente' => $i->nombre,
                    'Descripcion' => $i->observaciones,
                ];
            }
            $resultado[] = [
                'Id' => $estudiante->id,
                'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                'Codigo' => $estudiante->codigo_estudiante,
                'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                'CodigoCurso' => $estudiante->codigo_curso,
                'Horario' => $estudiante->horario,
                'Riesgo' => $estudiante->riesgo,
                'Fecha' => $estudiante->fecha,
                'Informes' => $informes_estudiante
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
            if($i->estado == 'Pendiente') continue;
            $resultado[] = [
                'IdInforme' => $i->id,
                'Estado' => $i->estado,
                'Fecha' => $i->fecha,
                'Desempeño' => $i->desempenho,
                'Observaciones' => $i->observaciones,
                'Docente' => $i->nombre
            ];
        }
        return response()->json($resultado);
    }

    public function agregar_informe_estudiante($numero_semana, $IdAlumnoRiesgo)
    {
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = new DateTime($ciclo->fecha_inicio);
        $fechaInicio->modify("+{$numero_semana} weeks");
        InformeRiesgo::create([
            'semana' => $numero_semana,
            'estado' => 'Pendiente',
            'fecha' => $fechaInicio,
            'codigo_alumno_riesgo' => $IdAlumnoRiesgo,
        ]);
    }

    public function crear_informes(Request $request)
    {
        try{
            $request->validate([
                'NumeroSemana'=> 'required',
                'IdEspecialidad' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del creado de informes', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $numero_semana = $request->NumeroSemana;
        $especialidad = $request->IdEspecialidad;
        $ciclo = Semestre::where('estado', 'activo')->first();
        $periodo = $ciclo->anho . "-" . $ciclo->periodo;
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidad)->where('ciclo', $periodo)->get();
        foreach ($estudiantesRiesgo as $est){
            $this->agregar_informe_estudiante($numero_semana, $est->id);
        }
    }

    public function actualizar_informe_estudiante(Request $request)
    {
        try{
            $request->validate([
                'IdInforme'=> 'required',
                'Desempeño' => 'required',
                'Observaciones' => 'required',
                'NombreProfesor' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del actualizado de informes', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $informe = InformeRiesgo::find($request->IdInforme);
        $informe->desempenho = $request->Desempeño;
        $informe->observaciones = $request->Observaciones;
        $informe->estado = 'Respondida';
        $informe->nombre_profesor = $request->NombreProfesor;
        $informe->save();
        return response()->json("",201);
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
        try{
            $request->validate([
                'CodigoProfesor'=> 'required',
                'Especialidad' => 'required',
                'Estado' => 'required',
                'Riesgo' => 'required',
                'Busqueda' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del listado de alumnos en riesgo', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
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
            Log::channel('usuarios')->info('Error al validar los datos de subida de alumnos en riesgo', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $ciclo = Semestre::where('estado', 'activo')->first();
        foreach ($request->alumnos as $alumno){
            try {
                EstudianteRiesgo::create([
                    'codigo_estudiante' => $alumno['Codigo'],
                    'codigo_curso' => Curso::where('cod_curso', $alumno['CodigoCurso'])->first()->id,
                    'codigo_especialidad' => $request->Especialidad,
                    'horario' => $alumno['Horario'],
                    'riesgo' => $alumno['Riesgo'],
                    'fecha' => $alumno['Fecha'],
                    'ciclo' => $ciclo->anho . "-" . $ciclo->periodo,
                ]);
            }catch (ValidationException $e){
                Log::channel('usuarios')->info('Error al cargar el alumno', ['error' => $e->errors()]);
                return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
                continue;
            }
        }
        return response()->json("",201);
    }

    public function obtener_estadisticas_informes(Request $request)
    {
        try {
            $especialidad = $request->IdEspecialidad;
            // Verifica si el ID de especialidad se recibe
            if (is_null($especialidad)) {
                return response()->json(['error' => 'IdEspecialidad is required'], 400);
            }
            // Obtener todos los estudiantes en riesgo para la especialidad dada
            $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidad)->get();

            // Estructura de datos para almacenar los informes agrupados
            $dataPorInforme = [];
            $desempenhoLabels = ['Mal', 'Regular', 'Bien'];

            // Recopilar todos los informes de los estudiantes en riesgo
            $informes = collect();
            foreach ($estudiantesRiesgo as $estudiante) {
                $informes = $informes->merge($estudiante->informes);
            }

            // Agrupar los informes por semana y ordenar las semanas
            $informesPorSemana = $informes->groupBy('semana')->sortKeys();

            // Recorrer cada grupo de informes (por semana)
            $semanaIndex = 1;
            foreach ($informesPorSemana as $semana => $informesDeSemana) {
                // Contar estadísticas de desempeño
                $estadisticas = [
                    'Mal' => $informesDeSemana->where('desempenho', 'Mal')->count(),
                    'Regular' => $informesDeSemana->where('desempenho', 'Regular')->count(),
                    'Bien' => $informesDeSemana->where('desempenho', 'Bien')->count(),
                ];
                $total = array_sum($estadisticas);

                // Crear datos para el gráfico de pastel
                $pieData = [
                    'labels' => $desempenhoLabels,
                    'datasets' => [
                        [
                            'data' => array_values($estadisticas),
                        ]
                    ]
                ];

                // Calcular completitud como el porcentaje de informes respondidos
                $totalInformes = $informesDeSemana->count();
                $totalRespondidos = $informesDeSemana->where('estado', 'Respondida')->count();
                $completitud = $totalInformes > 0 ? ($totalRespondidos / $totalInformes) * 100 : 0;

                // Almacenar los datos en la estructura final
                $dataPorInforme['informe' . ($semanaIndex)] = [
                    'estadisticas' => [
                        ['label' => 'Mal', 'value' => $estadisticas['Mal']],
                        ['label' => 'Regular', 'value' => $estadisticas['Regular']],
                        ['label' => 'Bien', 'value' => $estadisticas['Bien']],
                        ['label' => 'Total', 'value' => $total],
                    ],
                    'pieData' => $pieData,
                    'completitud' => round($completitud, 0) // Redondear el porcentaje de completitud a entero
                ];

                $semanaIndex++;
            }

            // Convertir $dataPorInforme en JSON
            return response()->json($dataPorInforme);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
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
