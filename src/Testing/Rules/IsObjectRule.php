<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsObjectRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'object';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return is_object((object) $this->value);
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is not a valid object, :unitType returned.';
    }
}
