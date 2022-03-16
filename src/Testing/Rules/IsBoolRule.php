<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsBoolRule extends IsBooleanRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'bool';
}
