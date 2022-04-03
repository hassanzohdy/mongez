<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class IsDateTimeRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'isDate';

    /**
     * Determine if the rule is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return (bool) DateTime::createFromFormat('d-m-Y H:i:s A', $this->value);
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key is expected to be d-m-Y H:i:s A, :valueType returned.';
    }
}
