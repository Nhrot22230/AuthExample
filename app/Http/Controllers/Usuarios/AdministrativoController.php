<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\Administrativo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdministrativoController extends Controller
{
    public function index()
    {
        $per_page = request('per_page', 10);
        $search = request('search', '');

        $administrativos = Administrativo::with('usuario')
            ->whereHas('usuario', function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $search . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orWhere('codigoAdministrativo', 'like', '%' . $search . '%')
            ->paginate($per_page);

        return response()->json($administrativos, 200);
    }

    public function show($codigo)
    {
        $administrativo = Administrativo::with('usuario')->where('codigoAdministrativo', $codigo)->first();
        if (!$administrativo) {
            return response()->json(['message' => 'Administrativo no encontrado'], 404);
        }
        return response()->json($administrativo, 200);
    }

    public function update(Request $request, $codigo)
    {
        $administrativo = Administrativo::with('usuario')->where('codigoAdministrativo', $codigo)->first();
        if (!$administrativo) {
            return response()->json(['message' => 'Administrativo no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $administrativo->usuario_id,
            'password' => 'nullable|string|min:8',
            'lugarTrabajo' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'codigoAdministrativo' => 'required|string|max:50|unique:administrativos,codigoAdministrativo,' . $administrativo->id,
            'facultad_id' => 'nullable|exists:facultades,id',
        ]);

        DB::transaction(function () use ($validatedData, $administrativo) {
            $usuarioData = [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'email' => $validatedData['email'],
            ];
            if (!empty($validatedData['password'])) {
                $usuarioData['password'] = Hash::make($validatedData['password']);
            }
            $administrativo->usuario->update($usuarioData);
            $administrativo->update([
                'lugarTrabajo' => $validatedData['lugarTrabajo'],
                'cargo' => $validatedData['cargo'],
                'codigoAdministrativo' => $validatedData['codigoAdministrativo'],
                'facultad_id' => $validatedData['facultad_id'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Administrativo actualizado exitosamente',
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'codigoAdministrativo' => 'required|string|max:50|unique:administrativos,codigoAdministrativo',
            'lugarTrabajo' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'facultad_id' => 'nullable|exists:facultades,id', // Validación opcional de facultad_id
        ]);

        $usuario = Usuario::firstOrCreate(
            ['email' => $validatedData['email']],
            [
                'nombre' => $validatedData['nombre'],
                'apellido_paterno' => $validatedData['apellido_paterno'],
                'apellido_materno' => $validatedData['apellido_materno'],
                'password' => Hash::make($validatedData['codigoAdministrativo']),
            ]
        );

        $administrativo = new Administrativo();
        $administrativo->usuario_id = $usuario->id;
        $administrativo->codigoAdministrativo = $validatedData['codigoAdministrativo'];
        $administrativo->lugarTrabajo = $validatedData['lugarTrabajo'];
        $administrativo->cargo = $validatedData['cargo'];
        $administrativo->facultad_id = $validatedData['facultad_id'] ?? null; // Asignación de facultad_id si está presente
        $usuario->administrativo()->save($administrativo);

        return response()->json(['message' => 'Administrativo creado exitosamente', 'administrativo' => $administrativo], 201);
    }



    public function storeMultiple(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'administrativos' => 'required|array',
                'administrativos.*.Codigo' => 'required|string|max:50|unique:administrativos,codigoAdministrativo',
                'administrativos.*.Nombre' => 'required|string|max:255',
                'administrativos.*.ApellidoPaterno' => 'nullable|string|max:255',
                'administrativos.*.ApellidoMaterno' => 'nullable|string|max:255',
                'administrativos.*.Email' => 'required|string|email|max:255',
                'administrativos.*.LugarTrabajo' => 'required|string|max:255',
                'administrativos.*.Cargo' => 'required|string|max:255',
                'administrativos.*.facultad_id' => 'nullable|exists:facultades,id', // Validación opcional de facultad_id
            ]);
        }
        catch (\Exception $e) {
            Log::channel('errors')->error('Error al validar los datos para crear múltiples administrativos: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar la solicitud'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($validatedData['administrativos'] as $administrativoData) {
                $usuario = Usuario::firstOrCreate(
                    ['email' => $administrativoData['Email']],
                    [
                        'nombre' => $administrativoData['Nombre'],
                        'apellido_paterno' => $administrativoData['ApellidoPaterno'],
                        'apellido_materno' => $administrativoData['ApellidoMaterno'],
                        'password' => Hash::make($administrativoData['Codigo']),
                    ]
                );

                $administrativo = new Administrativo();
                $administrativo->usuario_id = $usuario->id;
                $administrativo->codigoAdministrativo = $administrativoData['Codigo'];
                $administrativo->lugarTrabajo = $administrativoData['LugarTrabajo'];
                $administrativo->cargo = $administrativoData['Cargo'];
                $administrativo->facultad_id = $administrativoData['facultad_id'] ?? null; // Asignación opcional de facultad_id
                $usuario->administrativo()->save($administrativo);
            }
            DB::commit();
            return response()->json(['message' => 'Administrativos creados exitosamente'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('errors')->error('Error al crear múltiples administrativos: ' . $e->getMessage());
            return response()->json(['message' => 'Error al procesar la solicitud'], 420);
        }
    }
}
