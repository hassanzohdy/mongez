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
        if (!is_array($this->value)) return false;

        if (count($this->value) === 0) return true;

        return isset($this->value[0]);
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorMessage(): string
    {
        return ':key is not array, :valueType returned';
    }
}
