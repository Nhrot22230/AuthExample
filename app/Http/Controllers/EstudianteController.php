<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EstudianteController extends Controller
{
    public function index()
    {
        $estudiantes = Estudiante::with(['usuario', 'especialidad.facultad'])->get();

        return response()->json($estudiantes, 200);
    }
}
