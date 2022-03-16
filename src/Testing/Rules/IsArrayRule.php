<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsArrayRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'isArray';

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return is_array($this->value) && !is_object((object) $this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorMessage(): string
    {
        return ':key is not array, :valueType returned';
    }
}
