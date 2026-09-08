<?php

namespace App\Domain\Investment;

use DomainException;

class InvalidInvestmentDate extends DomainException
{
    public function __construct(string $message = 'The valuation date cannot be before the investment creation date.')
    {
        parent::__construct($message);
    }
}
