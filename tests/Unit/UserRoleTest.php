<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_enum_has_admin_and_owner_cases_only(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('owner', UserRole::Owner->value);
        $this->assertCount(2, UserRole::cases());
    }
}
