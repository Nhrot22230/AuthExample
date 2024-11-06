<?php

namespace Tests\Unit\Models\Authorization;

use App\Models\Authorization\Role;
use App\Models\Authorization\Scope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Test;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_role_has_many_scopes()
    {
        $role = Role::factory()->create();
        $scopes = Scope::factory()->count(3)->create();
        $role->scopes()->attach($scopes);

        $this->assertCount(3, $role->scopes);
    }
}
