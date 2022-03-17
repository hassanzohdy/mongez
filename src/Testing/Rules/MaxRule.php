<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\MissingUnitRuleOptionsException;
use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class MaxRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'max';

    /**
     * {@inheritDoc}
     */
    public function beforeValidating()
    {
        if (!isset($this->options[0])) {
            throw new MissingUnitRuleOptionsException(sprintf('max rule needs a max value to compare the given value with.'));
        }
    }

    /**
     * Determine if the rule is valid
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return (float) $this->value <= (float) $this->options[0];
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key\'s value is :value, expected to be :min or less.';
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageAttributes(): array
    {
        return [
            'max' => $this->options[0]
        ];
    }
}
