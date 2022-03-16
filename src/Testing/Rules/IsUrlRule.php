<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsUrlRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'url';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return filter_var((string) $this->value, FILTER_VALIDATE_URL);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not a valid url, :valueType returned.';
    }
}
