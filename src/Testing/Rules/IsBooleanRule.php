<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsBooleanRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'boolean';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return is_bool($this->value);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not bool, :valueType returned.';
    }
}
