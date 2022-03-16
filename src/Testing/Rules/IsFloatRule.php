<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsFloatRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'float';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return is_float($this->value);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not float, :valueType returned.';
    }
}
