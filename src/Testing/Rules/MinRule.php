<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\MissingUnitRuleOptionsException;
use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class MinRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'min';

    /**
     * {@inheritDoc}
     */
    public function beforeValidating()
    {
        if (empty($this->options[0])) {
            throw new MissingUnitRuleOptionsException(sprintf('min rule needs a min value to compare the given value with.'));
        }
    }

    /**
     * Determine if the rule is valid
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return (float) $this->value >= (float) $this->options[0];
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return ':key\'s value is :value, expected to be :min or more.';
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageAttributes(): array
    {
        return [
            'min' => $this->options[0]
        ];
    }
}
