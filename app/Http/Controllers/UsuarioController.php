<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    //
    public function index()
    {
        $usuarios = Usuario::all();

        return response()->json($usuarios, 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required',
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 400);
        }

        $usuario = Usuario::create($request->all());

        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            return response()->json($usuario, 200);
        }

        return response()->json(null, 404);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            $usuario->update($request->all());

            return response()->json($usuario, 200);
        }

        return response()->json(null, 404);
    }

    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if ($usuario) {
            $usuario->delete();

            return response()->json(null, 204);
        }

        return response()->json(null, 404);
    }
}
