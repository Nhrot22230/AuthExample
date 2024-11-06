<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'nullable|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $this->route('id'),
            'password' => 'nullable|string|min:8',
            'estado' => 'nullable|string|max:50',
            'google_id' => 'nullable|string|max:255',
            'picture' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El campo email es obligatorio.',
            'email.unique' => 'El email ya está en uso.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors()
        ], 422));
    }
}
