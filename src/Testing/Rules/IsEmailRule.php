<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsEmailRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'email';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return (bool) filter_var((string) $this->value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not valid email address, :valueType returned.';
    }
}
