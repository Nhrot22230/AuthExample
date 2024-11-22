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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Storage\File;
// importamos el file controller
use App\Http\Controllers\Storage\FileController;

class ConvocatoriaController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $perPage = (int) request('per_page', 10); // Número de resultados por página
            $search = request('search', '');   // Término de búsqueda
            $secciones = request('secciones', []); // Array de IDs de secciones
            $filters = request('filters', []);  // Array de estados (por ejemplo, ['abierta', 'cerrada'])
            $miembroId = request('miembro_id', null); // ID del miembro (opcional)
            $postulanteId = request('postulante_id', null); // ID del postulante (opcional)
            $noInscrito = filter_var(request('no_inscrito', false), FILTER_VALIDATE_BOOLEAN); // Filtro de no inscripción

            $convocatorias = Convocatoria::with(['gruposCriterios', 'comite', 'seccion'])
                ->withCount('candidatos') // Contar los candidatos
                ->when($search, function ($query, $search) {
                    $query->where('nombre', 'like', "%$search%");
                })
                ->when(!empty($secciones), function ($query) use ($secciones) {
                    $query->whereIn('seccion_id', $secciones); // Filtrar por secciones
                })
                ->when(!empty($filters), function ($query) use ($filters) {
                    $query->whereIn('estado', $filters); // Filtrar por estados
                })
                ->when($miembroId, function ($query) use ($miembroId) {
                    $query->whereHas('comite', function ($subQuery) use ($miembroId) {
                        $subQuery->whereHas('usuario', function ($userQuery) use ($miembroId) {
                            $userQuery->where('usuarios.id', $miembroId);
                        });
                    });
                })
                ->when($postulanteId && !$noInscrito, function ($query) use ($postulanteId) {
                    // Filtrar convocatorias donde el usuario es postulante
                    $query->whereHas('candidatos', function ($subQuery) use ($postulanteId) {
                        $subQuery->where('usuarios.id', $postulanteId);
                    });
                })
                ->when($postulanteId && $noInscrito, function ($query) use ($postulanteId) {
                    // Filtrar convocatorias donde el usuario NO es postulante
                    $query->whereDoesntHave('candidatos', function ($subQuery) use ($postulanteId) {
                        $subQuery->where('usuarios.id', $postulanteId);
                    });
                })

                ->paginate($perPage);

            return response()->json($convocatorias, 200);
        } catch (\Exception $e) {
            // Registrar error
            Log::error('Error al listar convocatorias:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Ocurrió un error al listar convocatorias'], 500);
        }
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
                ->select('usuarios.*', 'candidato_convocatoria.estadoFinal')
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
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'fechaEntrevista' => 'nullable|date',
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'integer|exists:docentes,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'criteriosAntiguos' => 'array',
            'criteriosAntiguos.*' => 'integer|exists:grupos_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);

        Log::info('Datos validados recibidos:', $validatedData);

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::create([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'fechaEntrevista' => $validatedData['fechaEntrevista'] ?? null,
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            if (!empty($validatedData['criteriosAntiguos'])) {
                $convocatoria->gruposCriterios()->attach($validatedData['criteriosAntiguos']);
            }

            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $convocatoria->gruposCriterios()->attach($nuevoCriterio->id);
                }
            }

            $convocatoria->comite()->attach($validatedData['miembros']);
            Log::info('Miembros asignados al comité');
            Log::info($validatedData['miembros']);
            // Crear registros en la tabla comite_candidato_convocatoria
            foreach ($validatedData['miembros'] as $miembroId) {
                foreach ($convocatoria->candidatos as $candidato) {
                    ComiteCandidatoConvocatoria::create([
                        'docente_id' => $miembroId,
                        'candidato_id' => $candidato->id,
                        'convocatoria_id' => $convocatoria->id,
                        'estado' => 'pendiente cv',
                    ]);
                }
            }
            Log::info('Miembros asignados a los candidatos');
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
            'fechaInicio' => 'required|date|before_or_equal:fechaFin',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'fechaEntrevista' => 'nullable|date',
            'criteriosAntiguos' => 'array',
            'criteriosAntiguos.*' => 'integer|exists:grupos_criterios,id',
            'criteriosNuevos' => 'array',
            'criteriosNuevos.*.nombre' => 'required|string|max:255',
            'criteriosNuevos.*.obligatorio' => 'required|boolean',
            'criteriosNuevos.*.descripcion' => 'nullable|string|max:1000',
            'seccion_id' => 'required|integer|exists:secciones,id',
            'miembros' => 'nullable|array', // Miembros es opcional
            'miembros.*' => 'integer|exists:docentes,id',
        ]);

        DB::beginTransaction();
        try {
            $convocatoria = Convocatoria::find($id);

            if (!$convocatoria) {
                return response()->json(['message' => 'Convocatoria no encontrada'], 404);
            }

            // Actualizar convocatoria
            $convocatoria->update([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'fechaEntrevista' => $validatedData['fechaEntrevista'] ?? null,
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            // Actualizar criterios
            $convocatoria->gruposCriterios()->detach();
            if (!empty($validatedData['criteriosAntiguos'])) {
                $convocatoria->gruposCriterios()->attach($validatedData['criteriosAntiguos']);
            }
            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $convocatoria->gruposCriterios()->attach($nuevoCriterio->id);
                }
            }
            Log::info('Criterios actualizados');
            Log:: info($validatedData['miembros']);
            if (!empty($validatedData['miembros'])) {
                $convocatoria->comite()->sync($validatedData['miembros']);

                // Eliminar registros existentes en comite_candidato_convocatoria
                ComiteCandidatoConvocatoria::where('convocatoria_id', $convocatoria->id)->delete();

                // Crear nuevos registros en comite_candidato_convocatoria
                foreach ($validatedData['miembros'] as $miembroId) {
                    foreach ($convocatoria->candidatos as $candidato) {
                        ComiteCandidatoConvocatoria::create([
                            'docente_id' => $miembroId,
                            'candidato_id' => $candidato->id,
                            'convocatoria_id' => $convocatoria->id,
                            'estado' => 'pendiente cv',
                        ]);
                    }
                }
            }

            Log :: info('Miembros actualizados');
            DB::commit();

            return response()->json([
                'message' => 'Convocatoria actualizada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al actualizar la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Error al actualizar la convocatoria.'], 500);
        }
    }

    private function sincronizarComiteCandidatos(Convocatoria $convocatoria, array $miembros)
    {
        // Eliminar registros existentes
        ComiteCandidatoConvocatoria::where('convocatoria_id', $convocatoria->id)->delete();

        // Crear nuevos registros
        foreach ($miembros as $miembroId) {
            foreach ($convocatoria->candidatos as $candidato) {
                ComiteCandidatoConvocatoria::create([
                    'docente_id' => $miembroId,
                    'candidato_id' => $candidato->id,
                    'convocatoria_id' => $convocatoria->id,
                    'estado' => 'pendiente cv',
                ]);
            }
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
                'grupo_criterios' => $grupoCriterios,
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
                'grupo_criterios' => $grupoCriterios,
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

    public function addCandidatoToConvocatoria(Request $request)
    {
        $validatedData = $request->validate([
            'convocatoria_id' => 'required|exists:convocatoria,id', // Verifica que el ID de convocatoria exista
            'candidato_id' => 'required|exists:usuarios,id', // Verifica que el ID del candidato exista
            'documento' => 'required|file|mimes:jpeg,png,zip,jpg,gif,mp4,mkv,mp3,wav,pdf,doc,docx,webp|max:2048',
        ]);

        try {
            $existingRelation = CandidatoConvocatoria::where('convocatoria_id', $validatedData['convocatoria_id'])
                ->where('candidato_id', $validatedData['candidato_id'])
                ->first();

            if ($existingRelation) {
                return response()->json(['message' => 'El candidato ya está relacionado con esta convocatoria.'], 400);
            }
            $file = $request->file('documento');
            if (!$file || !$file->isValid()) {
                return response()->json(['message' => 'El archivo no es válido o no se ha recibido.'], 400);
            }

            $uploadRequest = new Request([
                'name' => $file->getClientOriginalName(),
                'file_type' => 'document',
                'file' => $file
            ]);

            $uploadRequest->files->set('file', $file);
            $fileResponse = $this->subirArchivo($uploadRequest);

            if ($fileResponse->status() !== 201) {
                return response()->json(['message' => 'Error al subir el archivo.'], 500);
            }

            $fileId = $fileResponse->getData()->file->id;

            // Crea la relación
            $candidatoConvocatoria = CandidatoConvocatoria::create([
                'convocatoria_id' => $validatedData['convocatoria_id'],
                'candidato_id' => $validatedData['candidato_id'],
                'estadoFinal' => 'pendiente cv', // Estado inicial automático
                'file_id' => $fileId,
            ]);

            $convocatoria = Convocatoria::find($validatedData['convocatoria_id']);
            $miembros = $convocatoria->comite->pluck('id');

            foreach ($miembros as $miembro) {
                ComiteCandidatoConvocatoria::create([
                    'docente_id' => $miembro,
                    'candidato_id' => $validatedData['candidato_id'],
                    'convocatoria_id' => $validatedData['convocatoria_id'],
                    'estado' => 'pendiente cv',
                ]);
            }

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

    private function subirArchivo(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'file_type' => ['required', Rule::in(['image', 'video', 'audio', 'document'])],
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mkv,mp3,wav,pdf,doc,zip,docx,webp|max:2048', // tamaño máximo en KB
            ]);

            $file = $request->file('file');
            $uniqueName = time() . '_' . $file->getClientOriginalName();
            $path = 'files/' . $request->file_type . '/' . $uniqueName . '.' . $file->getClientOriginalExtension();

            Storage::disk('s3')->put($path, file_get_contents($file));
            // $url = 'https://' . env('AWS_BUCKET') . '.s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . $path;

            $fileRecord = File::create([
                'name' => $uniqueName,
                'file_type' => $request->file_type,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'path' => $path,
                'url' => Storage::url($path),
            ]);

            return response()->json(['url' => $fileRecord->url, 'file' => $fileRecord], 201);
        } catch (\Exception $e) {
            Log::error('File Upload Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading file: ' . $e->getMessage()], 500);
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

    /**
     * Fetch all candidates for a given committee member and a specific call.
     */
    public function fetchCandidatesByCommitteeMember(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Número de resultados por página
        $search = $request->input('search', '');   // Término de búsqueda
        $miembroId = $request->input('miembro_id'); // ID del miembro del comité
        $convocatoriaId = $request->input('convocatoria_id'); // ID de la convocatoria

        // Validar los parámetros requeridos
        if (!$miembroId || !$convocatoriaId) {
            return response()->json(['message' => 'El ID del miembro del comité y el ID de la convocatoria son obligatorios.'], 400);
        }

        try {
            // Consultar candidatos con filtros
            $candidatos = ComiteCandidatoConvocatoria::with(['candidato', 'miembroComite', 'convocatoria'])
                ->where('docente_id', $miembroId) // Filtrar por miembro del comité
                ->where('convocatoria_id', $convocatoriaId) // Filtrar por convocatoria
                ->when($search, function ($query, $search) {
                    // Filtrar por término de búsqueda en los datos del candidato
                    $query->whereHas('candidato', function ($subQuery) use ($search) {
                        $subQuery->where('nombre', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    });
                })
                ->paginate($perPage);

            return response()->json($candidatos, 200);
        } catch (\Exception $e) {
            // Registrar el error para depuración
            Log::error('Error al obtener los candidatos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Error al obtener los candidatos.'], 500);
        }
    }
}
