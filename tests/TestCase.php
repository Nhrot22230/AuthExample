<?php

namespace Tests;

use App\Models\Usuarios\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    /**
     * Sobrescribe el método actingAs para incluir el token JWT en la cabecera de autorización.
     *
     * @param  \App\Models\Usuarios\Usuario $user
     * @param  string|null $driver
     * @return $this
     */
    public function actingAs($user, $driver = null)
    {
        $token = JWTAuth::fromUser($user);

        $this->withHeaders(['Authorization' => "Bearer $token"]);
        return parent::actingAs($user, $driver);
    }

    /**
     * Obtiene un usuario aleatorio de la base de datos.
     *
     * @return \App\Models\Usuarios\Usuario
     */
    public function getRandomUser()
    {
        return Usuario::inRandomOrder()->first() ?? Usuario::factory()->create();
    }
}
