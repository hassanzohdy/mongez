<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsIntRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'isInt';

    /**
     * Determine if the rule is valid
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return is_int($this->value);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not integer, :valueType returned.';
    }
}
