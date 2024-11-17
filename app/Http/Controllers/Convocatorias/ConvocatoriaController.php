<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use App\Models\Convocatorias\CandidatoConvocatoria;
use App\Models\Convocatorias\ComiteCandidatoConvocatoria;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\GrupoCriterios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConvocatoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 10); // Número de resultados por página
        $search = request('search', '');   // Término de búsqueda
        $secciones = request('secciones', []); // Array de IDs de secciones
        $filters = request('filters', []);  // Array de estados (por ejemplo, ['abierta', 'cerrada'])

        $convocatorias = Convocatoria::with('gruposCriterios', 'comite')
            ->withCount('candidatos') // Agrega la cantidad de candidatos
            ->when($search, function ($query, $search) {
                $query->where('nombre', 'like', "%$search%");
            })
            ->when(!empty($secciones), function ($query) use ($secciones) {
                $query->whereIn('seccion_id', $secciones); // Filtra por secciones
            })
            ->when($filters, function ($query, $filters) {
                $query->whereIn('estado', $filters); // Filtra por estados
            })
            ->paginate($perPage);

        return response()->json($convocatorias, 200);
    }


    public function indexCriterios($entity_id)
    {
        if (!is_numeric($entity_id)) {
            return response()->json(['error' => 'Invalid entity ID.'], 400);
        }

        $perPage = request()->input('per_page', 10);
        $search = request()->input('search', '');

        try {
            $grupoCriterios = GrupoCriterios::with('convocatorias')
                ->whereHas('convocatorias', function ($query) use ($entity_id) {
                    $query->where('seccion_id', $entity_id);
                })
                ->when($search, function ($query, $search) {
                    $query->where('nombre', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->appends(request()->only(['search', 'per_page']));

            return response()->json($grupoCriterios, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving criteria', 'details' => $e->getMessage()], 500);
        }
    }


    public function listarConvocatoriasTodas()
    {
        try {
            $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')->get();

            if ($convocatorias->isEmpty()) {
                return response()->json(['message' => 'No se encontraron convocatorias'], 404);
            }

            return response()->json($convocatorias, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            // Busca la convocatoria por ID con las relaciones necesarias
            $convocatoria = Convocatoria::with('gruposCriterios', 'comite.usuario', 'candidatos', 'seccion')->find($id);

            // Verifica si la convocatoria existe
            if (!$convocatoria) {
                return response()->json(['message' => 'Convocatoria no encontrada'], 404);
            }

            return response()->json($convocatoria, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al obtener la convocatoria'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombreConvocatoria' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fechaEntrevista' => 'required|date|after_or_equal:fechaInicio',
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'fechaEntrevista' => 'nullable|date',
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:docentes,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'criteriosAntiguo' => 'array',
            'criteriosAntiguo.*' => 'integer|exists:grupos_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);
        Log::info('Datos validados recibidos:', $validatedData);

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::create([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaEntrevista' => $validatedData['fechaEntrevista'],
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'estado' => 'abierta', // Estado inicial
                'seccion_id' => $validatedData['seccion_id'],
            ]);


            if (!empty($validatedData['criteriosAntiguo'])) {
                $convocatoria->gruposCriterios()->attach($validatedData['criteriosAntiguo']);
            }


            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $convocatoria->gruposCriterios()->attach($nuevoCriterio->id);
                }
            }


            $convocatoria->comite()->attach($validatedData['miembros']);
            DB::commit();
            return response()->json([
                'message' => 'Convocatoria creada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombreConvocatoria' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'fechaEntrevista' => 'required|date|after_or_equal:fechaInicio',
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:docentes,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'criteriosAntiguo' => 'array',
            'criteriosAntiguo.*' => 'integer|exists:grupos_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);

        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID.'], 400);
        }

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::findOrFail($id);

            // Actualizar la convocatoria
            $convocatoria->update([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaEntrevista' => $validatedData['fechaEntrevista'],
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            // Manejo de criterios antiguos y nuevos
            $criteriosAntiguos = $validatedData['criteriosAntiguo'] ?? [];
            $criteriosNuevosIds = [];

            // Crear nuevos criterios
            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $criteriosNuevosIds[] = $nuevoCriterio->id;
                }
            }

            // Sincronizar los criterios de la convocatoria
            $convocatoria->gruposCriterios()->sync(array_merge($criteriosAntiguos, $criteriosNuevosIds));

            // Sincronizar miembros del comité (relación convocatoria-docente)
            $convocatoria->comite()->sync($validatedData['miembros']);

            // Obtener los candidatos asociados con esta convocatoria
            $candidatos = DB::table('candidato_convocatoria')
                ->where('convocatoria_id', $id)
                ->pluck('candidato_id')
                ->toArray();

            // Obtener los miembros actuales de la convocatoria
            $miembros = $validatedData['miembros'];

            // Insertar las relaciones entre docentes y candidatos
            $dataToInsert = [];

            foreach ($miembros as $docente_id) {
                foreach ($candidatos as $candidato_id) {
                    // Solo insertamos si la relación no existe
                    $dataToInsert[] = [
                        'docente_id' => $docente_id,
                        'candidato_id' => $candidato_id,
                        'convocatoria_id' => $id,
                        'estado' => 'pendiente cv',
                    ];
                }
            }

            // Realizamos la inserción masiva sin duplicados
            DB::table('comite_candidato_convocatoria')->upsert($dataToInsert, ['docente_id', 'candidato_id', 'convocatoria_id'], ['estado']);

            DB::commit();
            return response()->json([
                'message' => 'Convocatoria actualizada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeGrupoCriterios(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'obligatorio' => 'required|boolean',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $grupoCriterios = GrupoCriterios::create([
                'nombre' => $validatedData['nombre'],
                'obligatorio' => $validatedData['obligatorio'],
                'descripcion' => $validatedData['descripcion'] ?? null,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Grupo de criterios creado exitosamente.',
                'grupos_criterios' => $grupoCriterios,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear el grupo de criterios.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateGrupoCriterios(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'obligatorio' => 'required|boolean',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID.'], 400);
        }

        DB::beginTransaction();
        try {
            $grupoCriterios = GrupoCriterios::findOrFail($id);

            $grupoCriterios->update([
                'nombre' => $validatedData['nombre'],
                'obligatorio' => $validatedData['obligatorio'],
                'descripcion' => $validatedData['descripcion'] ?? null,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Grupo de criterios actualizado exitosamente.',
                'grupos_criterios' => $grupoCriterios,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el grupo de criterios.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerEstadoCandidato($idConvocatoria, $idCandidato)
    {
        if (!is_numeric($idConvocatoria)) {
            return response()->json(['error' => 'Invalid ID convocatoria.'], 400);
        }

        if (!is_numeric($idCandidato)) {
            return response()->json(['error' => 'Invalid ID candidato.'], 400);
        }

        try {
            // Busca el estado global del candidato en la convocatoria
            $estadoGlobal = DB::table('candidato_convocatoria')
                ->where('convocatoria_id', $idConvocatoria)
                ->where('candidato_id', $idCandidato)
                ->value('estadoFinal');
            
            if (empty($estadoGlobal)) {
                return response()->json(['message' => 'No se encontraró al candidato en la convocatoria.'], 404);
            }
            
            // Obtener estados parciales por miembro del comité
            $estados = DB::table('comite_candidato_convocatoria')
                ->where('convocatoria_id', $idConvocatoria)
                ->where('candidato_id', $idCandidato)
                ->select('docente_id', 'estado')
                ->get();

            $jsonResponse = [
                'estadoFinal' => $estadoGlobal,
                'estadosPorMiembroComite' => $estados->map(function ($estadoMiembro) {
                    return [
                        'id' => $estadoMiembro->docente_id,
                        'estadoMiembro' => $estadoMiembro->estado,
                    ];
                })->toArray(),
            ];
            
            return response()->json($jsonResponse, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener el estado del candidato:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al obtener el estado del candidato'], 500);
        }
    }

    public function actualizarEstado(Request $request, $idConvocatoria, $idCandidato)
    {
        // Verificar el tipo de parámetros que llegaron en el request
        if ($request->has('docente_id') && $request->has('estado')) {
            // Lógica para la actualización de estado del miembro del comité
            return $this->actualizarEstadoMiembroComite($request, $idConvocatoria, $idCandidato);
        } elseif ($request->has('estado')) {
            // Lógica para actualizar solo el estado del candidato
            return $this->actualizarEstadoCandidato($request, $idConvocatoria, $idCandidato);
        } else {
            return response()->json(['error' => 'Datos inválidos.'], 400);
        }
    }

    private function actualizarEstadoCandidato(Request $request, $idConvocatoria, $idCandidato)
    {
        $validatedData = $request->validate([
            'estado' => 'required|string'
        ]);

        if (!is_numeric($idConvocatoria)) {
            return response()->json(['error' => 'Invalid ID convocatoria.'], 400);
        }

        if (!is_numeric($idCandidato)) {
            return response()->json(['error' => 'Invalid ID candidato.'], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizamos el estado final del candidato en la tabla candidato_convocatoria
            $estadoFinal = $validatedData['estado'];
            $actualizado = CandidatoConvocatoria::where('convocatoria_id', $idConvocatoria)
                ->where('candidato_id', $idCandidato)
                ->update(['estadoFinal' => $estadoFinal]);

            // Si el estado global del candidato es "culminado entrevista", actualizamos todos los estados de los miembros del comité
            if ($estadoFinal === 'culminado entrevista') {
                ComiteCandidatoConvocatoria::where('convocatoria_id', $idConvocatoria)
                    ->where('candidato_id', $idCandidato)
                    ->update(['estado' => 'culminado entrevista']);
            }
            
            DB::commit();

            if ($actualizado) {
                return response()->json(['message' => 'Estado actualizado correctamente.', 200]);
            } else {
                return response()->json(['message' => 'No se encontró el registro para actualizar.'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el estado del candidato.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function actualizarEstadoMiembroComite(Request $request, $idConvocatoria, $idCandidato)
    {
        $validatedData = $request->validate([
            'docente_id' => 'integer|exists:docentes,id',
            'estado' => 'required|string'
        ]);

        if (!is_numeric($idConvocatoria)) {
            return response()->json(['error' => 'Invalid ID convocatoria.'], 400);
        }

        if (!is_numeric($idCandidato)) {
            return response()->json(['error' => 'Invalid ID candidato.'], 400);
        }

        DB::beginTransaction();
        try {
            $actualizado = ComiteCandidatoConvocatoria::where('convocatoria_id', $idConvocatoria)
                ->where('candidato_id', $idCandidato)
                ->where('docente_id', $validatedData['docente_id'])
                ->update(['estado' => $validatedData['estado']]);

            if ($actualizado) {
                // Verifica los estados parciales de todos los miembros del comité
                $estados = ComiteCandidatoConvocatoria::where('convocatoria_id', $idConvocatoria)
                    ->where('candidato_id', $idCandidato)
                    ->pluck('estado'); // Obtenemos solo los estados

                // Lógica para determinar el estado final del candidato
                $estadoFinal = $this->determinarEstadoFinal($estados);

                // Actualiza el estado final del candidato en la tabla candidato_convocatoria
                CandidatoConvocatoria::where('convocatoria_id', $idConvocatoria)
                    ->where('candidato_id', $idCandidato)
                    ->update(['estadoFinal' => $estadoFinal]);

                DB::commit();
                return response()->json(['message' => 'Estado actualizado correctamente.'], 200);
            } else {
                return response()->json(['message' => 'No se encontró el registro para actualizar.'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar el estado del candidato.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function determinarEstadoFinal($estados)
    {
        // 1. Si hay al menos un "pendiente cv", el estado final es "pendiente cv"
        if ($estados->contains('pendiente cv')) {
            return 'pendiente cv';
        }

        // 2. Si todos son "aprobado cv", el estado final es "aprobado cv"
        if ($estados->every(fn($estado) => $estado === 'aprobado cv')) {
            return 'aprobado cv';
        }

        // 3. Si al menos uno es "desaprobado cv" y ninguno es "pendiente cv", el estado final es "desaprobado cv"
        if ($estados->contains('desaprobado cv') && !$estados->contains('pendiente cv')) {
            return 'desaprobado cv';
        }

        // 4. Si hay al menos uno "culminado entrevista", el estado final es "culminado entrevista"
        if ($estados->contains('culminado entrevista')) {
            return 'culminado entrevista';
        }

        // 5. Si todos son "aprobado entrevista", el estado final es "aprobado entrevista"
        if ($estados->every(fn($estado) => $estado === 'aprobado entrevista')) {
            return 'aprobado entrevista';
        }

        // 6. Si al menos uno es "desaprobado entrevista" y ninguno es "culminado entrevista", el estado final es "desaprobado entrevista"
        if ($estados->contains('desaprobado entrevista') && !$estados->contains('culminado entrevista')) {
            return 'desaprobado entrevista';
        }

        // Si no se cumple ninguna de las condiciones anteriores, el estado final será "pendiente cv"
        return 'pendiente cv';
    }

    public function postularConvocatoria(Request $request, $id)
    {
        $validatedData = $request->validate([
            'candidato_id' => 'integer|exists:usuarios,id',
            'urlCV' => 'required|string|max:255'
        ]);

        if (!is_numeric($id)) {
            return response()->json(['error' => 'Invalid ID.'], 400);
        }

        // Verificar que la convocatoria exista en la base de datos
        $convocatoria = Convocatoria::find($id);
        if (!$convocatoria) {
            return response()->json(['error' => 'Convocatoria no encontrada.'], 404);
        }

        DB::beginTransaction();
        try {
            $candidato_convocatoria = CandidatoConvocatoria::create([
                'convocatoria_id' => $id,
                'candidato_id' => $validatedData['candidato_id'],
                'estadoFinal' => 'pendiente cv',
                'urlCV' => $validatedData['urlCV']
            ]);

            // Obtener todos los docentes asociados a la convocatoria
            $docentes = $convocatoria->comite;

            // Crear un registro en comite_candidato_convocatoria para cada docente
            $comiteCandidatoConvocatoriaData = [];
            foreach ($docentes as $docente) {
                $comiteCandidatoConvocatoriaData[] = [
                    'convocatoria_id' => $id,
                    'candidato_id' => $validatedData['candidato_id'],
                    'docente_id' => $docente->id,  // Usamos el ID del docente
                    'estado' => 'pendiente cv',  // Estado inicial
                    'created_at' => now(), // Fecha de creación
                    'updated_at' => now()  // Fecha de actualización
                ];
            }

            // Insertar todos los registros de una sola vez en la tabla comite_candidato_convocatoria
            ComiteCandidatoConvocatoria::insert($comiteCandidatoConvocatoriaData);

            DB::commit();
            return response()->json([
                'message' => 'Postulación realizada correctamente.',
                'candidato_convocatoria' => $candidato_convocatoria,
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al postular a la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
