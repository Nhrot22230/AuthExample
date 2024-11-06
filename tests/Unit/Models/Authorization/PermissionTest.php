<?php

namespace Tests\Unit\Models\Authorization;

use App\Models\Authorization\Permission;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Test;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_permission_belongs_to_a_category()
    {
        $permission_category = PermissionCategory::factory()->create();
        $permission = Permission::factory()->create(['permission_category_id' => $permission_category->id]);

        $this->assertTrue($permission->permission_category->is($permission_category));
    }
}
