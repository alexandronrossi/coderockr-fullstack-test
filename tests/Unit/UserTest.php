<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_fillable_excludes_role(): void
    {
        $this->assertNotContains('role', (new User)->getFillable());
        $this->assertSame(['name', 'email', 'password'], (new User)->getFillable());
    }

    public function test_fill_cannot_promote_owner_to_admin(): void
    {
        $user = new User;
        $user->forceFill(['role' => UserRole::Owner]);

        $user->fill(['role' => UserRole::Admin]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isOwner());
        $this->assertSame(UserRole::Owner, $user->role);
    }

    public function test_is_admin_is_fail_closed(): void
    {
        $owner = (new User)->forceFill(['role' => UserRole::Owner]);
        $admin = (new User)->forceFill(['role' => UserRole::Admin]);

        $this->assertFalse($owner->isAdmin());
        $this->assertTrue($owner->isOwner());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOwner());
    }
}
