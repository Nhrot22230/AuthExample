<?php

namespace App\Http\Controllers\EstudianteRiesgo;

use App\Http\Controllers\Controller;
use App\Models\EstudianteRiesgo\EstudianteRiesgo;
use App\Models\EstudianteRiesgo\InformeRiesgo;
use App\Models\Universidad\Curso;
use App\Models\Universidad\Semestre;
use App\Models\Usuarios\Estudiante;
use App\Models\Usuarios\Usuario;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Usuarios\Docente;

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

    public function crearInformePorEspecialidad(Request $request)
{
    // Validar los datos de entrada
    $request->validate([
        'semana' => 'required|integer',
        'fecha' => 'required|date',
        'especialidad_id' => 'required|integer'
    ]);

    // Obtener los datos del request
    $semana = $request->semana;
    $fecha = $request->fecha;
    $especialidad_id = $request->especialidad_id;

    // Obtener todos los estudiantes en riesgo de la especialidad indicada
    $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidad_id)->get();

    // Revisar si hay estudiantes en riesgo
    if ($estudiantesRiesgo->isEmpty()) {
        return response()->json(['message' => 'No hay estudiantes en riesgo para la especialidad especificada.'], 404);
    }

    // Insertar un informe de riesgo para cada estudiante en riesgo
    foreach ($estudiantesRiesgo as $estudiante) {
        InformeRiesgo::create([
            'codigo_alumno_riesgo' => $estudiante->id,
            'fecha' => $fecha,
            'semana' => $semana,
            'desempenho' => null,
            'observaciones' => null,
            'estado' => 'Pendiente',  // Establecer estado como "Pendiente"
            'nombre_profesor' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return response()->json(['message' => 'Informe(s) de riesgo creado(s) exitosamente.'], 201);
}

    public function obtenerSemanasPorEspecialidad(Request $request){
        // Validar que el id de la especialidad se haya enviado
        $request->validate([
            'especialidad_id' => 'required|integer'
        ]);

        // Obtener el id de la especialidad
        $especialidad_id = $request->especialidad_id;

        // Consultar semanas y fechas únicas para la especialidad indicada
        $semanas = InformeRiesgo::whereHas('estudianteRiesgo', function($query) use ($especialidad_id) {
                $query->where('codigo_especialidad', $especialidad_id);
            })
            ->select('semana', 'fecha')
            ->distinct()
            ->get();

        // Devolver los resultados en formato JSON
        return response()->json($semanas);
    }

    public function listar_por_especialidad_director(Request $request){
        try {
            $request->validate([
                'IdEspecialidad' => 'required|integer',
                'search' => 'nullable|string',
                'semana' => 'nullable|integer',
                'riesgo' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            Log::channel('usuarios')->info('Error al validar los datos del listado de alumnos en riesgo', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
    
        $especialidadId = $request->IdEspecialidad;
        $search = $request->search;
        $semana = $request->semana;
        $riesgo = $request->riesgo;
    
        // Obtener el ciclo activo
        $ciclo = Semestre::where('estado', 'activo')->first();
        if (!$ciclo) {
            return response()->json(['message' => 'No se encontró un semestre activo.'], 404);
        }
    
        $periodo = $ciclo->anho . "-" . $ciclo->periodo;
    
        // Obtener estudiantes en riesgo de la especialidad y ciclo especificados
        $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidadId)
            ->where('ciclo', $periodo);
    
        // Aplicar filtro por riesgo
        if (!empty($riesgo) && $riesgo !== 'Todos') {
            $estudiantesRiesgo->where('riesgo', $riesgo);
        }
    
        // Filtrar por búsqueda (nombre o código del estudiante)
        if (!empty($search)) {
            $estudiantesRiesgo->where(function ($query) use ($search) {
                $query->where('codigo_estudiante', 'like', '%' . $search . '%')
                    ->orWhereHas('usuario', function ($query) use ($search) {
                        $query->where('nombre', 'like', '%' . $search . '%');
                    });
            });
        }
    
        $estudiantesRiesgo = $estudiantesRiesgo->get();
    
        if ($estudiantesRiesgo->isEmpty()) {
            return response()->json([]);
        }
    
        $resultado = [];
        foreach ($estudiantesRiesgo as $estudiante) {
            $usuario = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);
    
            // Filtrar informes por estado "Respondida" y semana (si se proporciona)
            $informesFiltrados = $estudiante->informes
                ->where('estado', 'Respondida')
                ->when(!is_null($semana), function ($query) use ($semana) {
                    return $query->where('semana', $semana);
                });
    
            foreach ($informesFiltrados as $informe) {
                $resultado[] = [
                    'Id' => $estudiante->id,
                    'Estudiante' => $usuario->nombre . " " . $usuario->apellido_paterno,
                    'Codigo' => $estudiante->codigo_estudiante,
                    'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                    'CodigoCurso' => $estudiante->codigo_curso,
                    'Horario' => $estudiante->horario,
                    'Riesgo' => $estudiante->riesgo,
                    'IdInforme' => $informe->id,
                    'Estado' => $informe->estado,
                    'Semana' => $informe->semana,
                    'Fecha' => $informe->fecha,
                    'Desempeño' => $informe->desempenho,
                    'Observaciones' => $informe->observaciones,
                    'Docente' => $informe->nombre_profesor
                ];
            }
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

    public function listar_informes_estudiante(Request $request){
        try {
            $request->validate([
                'codigo_estudiante' => 'required|string',
                'id_horario' => 'required|integer',
                'id_curso' => 'required|integer',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }

        $codigoEstudiante = $request->codigo_estudiante;
        $idHorario = $request->id_horario;
        $idCurso = $request->id_curso;

        // Obtener el estudiante en riesgo correspondiente
        $estudianteRiesgo = EstudianteRiesgo::where('codigo_estudiante', $codigoEstudiante)
            ->where('horario', $idHorario)
            ->where('codigo_curso', $idCurso)
            ->first();

        if (!$estudianteRiesgo) {
            return response()->json(['message' => 'No se encontraron informes para el estudiante en el curso y horario proporcionados.'], 404);
        }

        // Obtener los informes asociados al estudiante en riesgo
        $informes = InformeRiesgo::where('codigo_alumno_riesgo', $estudianteRiesgo->id)->get();

        // Construir la respuesta
        $resultado = [];
        foreach ($informes as $informe) {
            $resultado[] = [
                'IdInforme' => $informe->id,
                'Estado' => $informe->estado,
                'Fecha' => $informe->fecha,
                'Desempeño' => $informe->desempenho,
                'Observaciones' => $informe->observaciones,
                'Docente' => $informe->nombre_profesor,
            ];
        }

        return response()->json($resultado);
    }



    public function agregar_informe_estudiante($numero_semana, $IdAlumnoRiesgo)
    {
        $numero_semana = (int)$numero_semana;
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = new DateTime($ciclo->fecha_inicio);
        $fechaInicio->modify("+{$numero_semana} weeks");
        $informeExistente = InformeRiesgo::where('semana', $numero_semana)
            ->where('codigo_alumno_riesgo', $IdAlumnoRiesgo)
            ->exists();
        if (!$informeExistente) {
            InformeRiesgo::create([
                'semana' => $numero_semana,
                'estado' => 'Pendiente',
                'fecha' => $fechaInicio,
                'codigo_alumno_riesgo' => $IdAlumnoRiesgo,
            ]);
        }
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
                'Docente' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos del actualizado de informes', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $informe = InformeRiesgo::find($request->IdInforme);
        $informe->desempenho = $request->Desempeño;
        $informe->observaciones = $request->Observaciones;
        $informe->estado = 'Respondida';
        $informe->nombre_profesor = $request->Docente;
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
            try {
                $request->validate([
                    'CodigoProfesor' => 'nullable',
                    'Especialidad' => 'nullable',
                    'Estado' => 'nullable', // "Pendiente" o "Realizada"
                    'Riesgo' => 'nullable', // "Tercera", "Cuarta" u "Otros"
                    'Busqueda' => 'nullable', // Código o nombre
                ]);
            } catch (ValidationException $e) {
                Log::channel('usuarios')->info('Error al validar los datos del listado de alumnos en riesgo', ['error' => $e->errors()]);
                return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
            }
        
            $profesor = $request->CodigoProfesor;
            $especialidad = $request->Especialidad;
            $estado = $request->Estado ?? 'Pendiente'; // Por defecto, filtrar pendientes
            $riesgo = $request->Riesgo ?? 'Todos';
            $busqueda = $request->Busqueda ?? '';
            $fechaActual = now();
        
            $estudiantesRiesgo = EstudianteRiesgo::where('codigo_especialidad', $especialidad)
                ->where('codigo_docente', $profesor)
                ->whereHas('informes', function ($query) use ($estado, $fechaActual) {
                    $query->where('estado', 'Pendiente')
                        ->where('fecha', '>=', $fechaActual); // Solo informes cuya fecha no haya pasado
                });
        
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
                return response()->json([]);
            }
        
            $resultado = [];
            foreach ($estudiantesRiesgo as $estudiante) {
                $est = Usuario::find(Estudiante::where('codigoEstudiante', $estudiante->codigo_estudiante)->first()->usuario_id);
                $informePendiente = $estudiante->informes
                    ->where('estado', 'Pendiente')
                    ->where('fecha', '>=', $fechaActual)
                    ->first(); // Obtener el informe pendiente más relevante
        
                if (!$informePendiente) continue;
        
                // Obtener el docente asociado al código_docente
                $docente = Docente::where('codigoDocente', $profesor)->first();
                $nombreDocente = $docente ? $docente->usuario->nombre . " " . $docente->usuario->apellido_paterno : 'No asignado';
        
                $resultado[] = [
                    'Id' => $estudiante->id,
                    'Estudiante' => $est->nombre . " " . $est->apellido_paterno,
                    'Codigo' => $estudiante->codigo_estudiante,
                    'Curso' => Curso::find($estudiante->codigo_curso)->nombre,
                    'CodigoCurso' => $estudiante->codigo_curso,
                    'Horario' => $estudiante->horario,
                    'Riesgo' => $estudiante->riesgo,
                    'IdInforme' => $informePendiente->id,
                    'Estado' => $informePendiente->estado,
                    'Fecha' => $informePendiente->fecha,
                    'Desempeño' => $informePendiente->desempenho,
                    'Observaciones' => $informePendiente->observaciones,
                    'Docente' => $nombreDocente
                ];
            }
        
            return response()->json($resultado);
        }
        
        

    public function crear_informes_estudiante($idEstudiante, $idEspecialidad)
    {
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = $ciclo->fecha_inicio;
        $fechaFin = $ciclo->fecha_fin;
        $informes = InformeRiesgo::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('alumno_riesgo', function ($query) use ($idEspecialidad) {
                $query->where('codigo_especialidad', $idEspecialidad);
            })
            ->pluck('semana')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        foreach ($informes as $i){
            $this->agregar_informe_estudiante((string)$i, $idEstudiante);
        }
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
                $nuevoEstudiante = EstudianteRiesgo::create([
                    'codigo_estudiante' => $alumno['Codigo'],
                    'codigo_curso' => Curso::where('cod_curso', $alumno['CodigoCurso'])->first()->id,
                    'codigo_especialidad' => $request->Especialidad,
                    'horario' => $alumno['Horario'],
                    'riesgo' => $alumno['Riesgo'],
                    'fecha' => $alumno['Fecha'],
                    'ciclo' => $ciclo->anho . "-" . $ciclo->periodo,
                ]);
                $this->crear_informes_estudiante($nuevoEstudiante->id, $request->Especialidad);
            }catch (ValidationException $e){
                Log::channel('usuarios')->info('Error al cargar el alumno', ['error' => $e->errors()]);
                //return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
                continue;
            }
        }
        return response()->json("",201);
    }

    public function listar_semanas_existentes($idEspecialidad)
    {
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = $ciclo->fecha_inicio;
        $fechaFin = $ciclo->fecha_fin;
        $informes = InformeRiesgo::whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->whereHas('alumno_riesgo', function ($query) use ($idEspecialidad) {
                    $query->where('codigo_especialidad', $idEspecialidad);
                    })
                ->pluck('semana')
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        $resultado = [];
        foreach ($informes as $i)
        {
            $resultado[] = [
              'Semana' => $i
            ];
        }
        return response()->json($resultado);
    }

    public function eliminar_semana(Request $request)
    {
        try {
            $request->validate([
                'Especialidad' => 'required',
                'Semana' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar los datos de eliminacion de semana en informes', ['error' => $e->errors()]);
            return response()->json(['message' => 'Datos inválidos: ' . $e->getMessage()], 400);
        }
        $idEspecialidad = $request->Especialidad;
        $semana = (int) $request->Semana;
        $ciclo = Semestre::where('estado', 'activo')->first();
        $fechaInicio = $ciclo->fecha_inicio;
        $fechaFin = $ciclo->fecha_fin;

        $deletedCount = InformeRiesgo::where('semana', $semana)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->whereHas('estudianteRiesgo', function ($query) use ($idEspecialidad) {
                $query->where('codigo_especialidad', $idEspecialidad);
            })
            ->delete();

        return response()->json("",201);
    }

    public function obtener_datos_semana(Request $request)
    {
        try {
            $request->validate([
                'Semana' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar la semana', ['error' => $e->errors()]);
            return response()->json(['message' => 'Dato inválido: ' . $e->getMessage()], 400);
        }

        try {
            // Obtener el valor de Semana del request
            $semanaInput = $request->Semana;

            // Inicializar la variable para almacenar la semana
            $semana = null;

            // Comprobar si el input es un entero
            if (is_numeric($semanaInput)) {
                $semana = (int)$semanaInput; // Convertir directamente a entero
            } else if (preg_match('/^Semana (\d+)$/i', $semanaInput, $matches)) {
                // Si el input tiene el formato exacto "Semana X" (no sensible a mayúsculas)
                $semana = (int)$matches[1];
            } else if (preg_match('/^(\d+)$/', $semanaInput, $matches)) {
                // Si el input tiene solo el número "X" como cadena, asegurando que sea solo dígitos
                $semana = (int)$matches[1];
            }

            // Verificar si se obtuvo un número de semana válido
            if ($semana === null) {
                return response()->json(['error' => 'Formato de semana inválido.'], 400);
            }

            // Obtener un informe programado en la semana especificada
            $informe = InformeRiesgo::where('semana', $semana)->first();

            if (!$informe) {
                return response()->json(['error' => 'No se encontró un informe para la semana especificada.'], 404);
            }

            // Obtener la fecha del informe de la semana especificada
            $fecha = new DateTime($informe->fecha);

            // Estructura final
            $data_semana = [
                'Semana' => 'Semana ' . $semana,
                'Fecha' => $this->convertir_formato_fecha($fecha),
                'Estado' => $fecha <= now() ? 'EJECUTADA' : 'NO EJECUTADA',
            ];

            return response()->json($data_semana);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    private function convertir_formato_fecha($fecha)
    {
        // Array de traducción de meses
        $mesesEspanol = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];

        // Formatea la fecha a "Mes Día"
        $mesIngles = $fecha->format('F');
        $dia = $fecha->format('d');

        // Traduce el mes al español y concatena con el día
        $mesEspanol = $mesesEspanol[$mesIngles] ?? $mesIngles;
        $fechaFormateada = "{$mesEspanol} {$dia}";

        return $fechaFormateada;
    }

    public function obtener_estadisticas_informes(Request $request)
    {
        try {
            $request->validate([
                'IdEspecialidad' => 'required',
            ]);
        } catch(ValidationException $e){
            Log::channel('usuarios')->info('Error al validar la especialidad', ['error' => $e->errors()]);
            return response()->json(['message' => 'Dato inválido: ' . $e->getMessage()], 400);
        }

        try {
            $especialidad = $request->IdEspecialidad;

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
