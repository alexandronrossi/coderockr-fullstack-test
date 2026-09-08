<?php

namespace App\Domain\Investment;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final class CivilMonthAnniversary
{
    public static function of(CarbonImmutable $createdOn, int $periodIndex): CarbonImmutable
    {
        if ($periodIndex < 0) {
            throw new InvalidArgumentException('Period index cannot be negative.');
        }

        $createdOn = $createdOn->startOfDay();

        if ($periodIndex === 0) {
            return $createdOn;
        }

        return $createdOn->addMonthsNoOverflow($periodIndex);
    }
}
