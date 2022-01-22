<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Contracts\Testing\UnitRule;
use HZ\Illuminate\Mongez\Managers\Testing\UnitRuleManager;

class IsFloat extends UnitRuleManager implements UnitRule
{
    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return 'isFloat';
    }

    /**
     * Determine if the rule is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid(mixed $value): bool
    {
        return is_float($value);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->key . ' must be float';
    }
}
