<?php

namespace Tests\Unit\Domain;

use App\Domain\Investment\CivilMonthAnniversary;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class CivilMonthAnniversaryTest extends TestCase
{
    public function test_period_zero_is_the_creation_date(): void
    {
        $created = CarbonImmutable::parse('2025-01-15');

        $this->assertTrue(CivilMonthAnniversary::of($created, 0)->isSameDay($created));
    }

    public function test_january_31_common_year_first_month_is_february_28(): void
    {
        $created = CarbonImmutable::parse('2025-01-31');

        $this->assertSame('2025-02-28', CivilMonthAnniversary::of($created, 1)->toDateString());
    }

    public function test_january_31_common_year_second_month_is_march_31(): void
    {
        $created = CarbonImmutable::parse('2025-01-31');

        $this->assertSame('2025-03-31', CivilMonthAnniversary::of($created, 2)->toDateString());
    }

    public function test_january_29_leap_year_february_is_the_29th(): void
    {
        $created = CarbonImmutable::parse('2024-01-29');

        $this->assertSame('2024-02-29', CivilMonthAnniversary::of($created, 1)->toDateString());
    }

    public function test_january_30_and_31_leap_year_february_is_the_29th(): void
    {
        $this->assertSame('2024-02-29', CivilMonthAnniversary::of(CarbonImmutable::parse('2024-01-30'), 1)->toDateString());
        $this->assertSame('2024-02-29', CivilMonthAnniversary::of(CarbonImmutable::parse('2024-01-31'), 1)->toDateString());
    }

    public function test_march_31_april_is_the_30th(): void
    {
        $created = CarbonImmutable::parse('2025-03-31');

        $this->assertSame('2025-04-30', CivilMonthAnniversary::of($created, 1)->toDateString());
    }
}
