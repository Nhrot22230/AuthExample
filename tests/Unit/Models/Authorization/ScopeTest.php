<?php

namespace Tests\Unit\Models\Authorization;

use App\Models\Authorization\Role;
use App\Models\Authorization\Scope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Test;
use Tests\TestCase;

class ScopeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_scope_has_many_roles()
    {
        $scope = Scope::factory()->create();
        $roles = Role::factory()->count(3)->create();
        $scope->roles()->attach($roles);

        $this->assertCount(3, $scope->roles);
    }
}
