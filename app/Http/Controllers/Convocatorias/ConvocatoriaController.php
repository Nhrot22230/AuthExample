<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
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

        $convocatorias = Convocatoria::with('gruposCriterios', 'comite', 'candidatos')
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


    public function listar_convocatorias_todas()
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
            'criteriosAntiguo.*' => 'integer|exists:grupo_criterios,id',
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
            'criteriosAntiguo.*' => 'integer|exists:grupo_criterios,id',
            'seccion_id' => 'required|integer|exists:secciones,id',
        ]);

        DB::beginTransaction();
        try {
            // Buscar la convocatoria
            $convocatoria = Convocatoria::find($id);

            if (!$convocatoria) {
                return response()->json(['message' => 'Convocatoria no encontrada'], 404);
            }

            // Actualizar atributos básicos
            $convocatoria->update([
                'nombre' => $validatedData['nombreConvocatoria'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'fechaInicio' => $validatedData['fechaInicio'],
                'fechaFin' => $validatedData['fechaFin'],
                'fechaEntrevista' => $validatedData['fechaEntrevista'] ?? null,
                'seccion_id' => $validatedData['seccion_id'],
            ]);

            // Actualizar criterios antiguos
            $convocatoria->gruposCriterios()->sync($validatedData['criteriosAntiguo'] ?? []);

            // Agregar criterios nuevos
            if (!empty($validatedData['criteriosNuevos'])) {
                foreach ($validatedData['criteriosNuevos'] as $criterioNuevo) {
                    $nuevoCriterio = GrupoCriterios::create($criterioNuevo);
                    $convocatoria->gruposCriterios()->attach($nuevoCriterio->id);
                }
            }

            // Actualizar miembros
            $convocatoria->comite()->sync($validatedData['miembros']);

            DB::commit();
            return response()->json([
                'message' => 'Convocatoria actualizada exitosamente.',
                'convocatoria' => $convocatoria->load('gruposCriterios', 'comite'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar la convocatoria:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            DB::rollBack();

            return response()->json([
                'message' => 'Error al actualizar la convocatoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
