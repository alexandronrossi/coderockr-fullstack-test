<?php

namespace App\Domain\Investment;

use DomainException;

class InvestmentAlreadyWithdrawn extends DomainException
{
    public function __construct(string $message = 'This investment has already been withdrawn.')
    {
        parent::__construct($message);
    }
}
