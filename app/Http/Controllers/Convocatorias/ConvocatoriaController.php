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
    public function getCandidatosByConvocatoria($id)
    {
        try {
            // Número de resultados por página (por defecto 10)
            $perPage = request('per_page', 10);

            // Término de búsqueda
            $search = request('search', '');

            // Busca la convocatoria por ID
            $convocatoria = Convocatoria::find($id);

            // Verifica si la convocatoria existe
            if (!$convocatoria) {
                return response()->json(['message' => 'Convocatoria no encontrada'], 404);
            }

            // Obtener candidatos con paginación y filtro de búsqueda
            $candidatos = $convocatoria->candidatos()
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nombre', 'like', "%$search%")
                            ->orWhere('apellido', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
                })
                ->paginate($perPage);

            return response()->json($candidatos, 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener los candidatos de la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al obtener los candidatos'], 500);
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

    public function cambiarEstadoMiembroComite(Request $request, $idConvocatoria, $idCandidato)
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

    public function addCandidatoToConvocatoria(Request $request)
    {
        $validatedData = $request->validate([
            'convocatoria_id' => 'required|exists:convocatoria,id', // Verifica que el ID de convocatoria exista
            'candidato_id' => 'required|exists:usuarios,id', // Verifica que el ID del candidato exista
            'urlCV' => 'nullable|string|max:255', // La URL del CV es opcional
        ]);

        try {
            // Verifica si la relación ya existe
            $existingRelation = CandidatoConvocatoria::where('convocatoria_id', $validatedData['convocatoria_id'])
                ->where('candidato_id', $validatedData['candidato_id'])
                ->first();

            if ($existingRelation) {
                return response()->json(['message' => 'El candidato ya está relacionado con esta convocatoria.'], 400);
            }

            // Crea la relación
            $candidatoConvocatoria = CandidatoConvocatoria::create([
                'convocatoria_id' => $validatedData['convocatoria_id'],
                'candidato_id' => $validatedData['candidato_id'],
                'estadoFinal' => 'pendiente cv', // Estado inicial automático
                'urlCV' => $validatedData['urlCV'] ?? null, // Si no se envía, será null
            ]);

            return response()->json([
                'message' => 'Candidato agregado a la convocatoria exitosamente.',
                'data' => $candidatoConvocatoria,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al agregar candidato a la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al agregar el candidato a la convocatoria.'], 500);
        }
    }
}
