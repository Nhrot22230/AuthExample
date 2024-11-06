<?php

namespace Tests\Unit\Models\Authorization;

use App\Models\Authorization\Permission;
use App\Models\Authorization\PermissionCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Test;
use Tests\TestCase;

class PermissionCategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_permission_category_has_many_permissions()
    {
        $permission_category = PermissionCategory::factory()->create();
        Permission::factory()->count(3)->create(['permission_category_id' => $permission_category->id]);

        $this->assertCount(3, $permission_category->permissions);
    }
}
