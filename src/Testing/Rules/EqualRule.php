<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use Exception;
use HZ\Illuminate\Mongez\Testing\MissingUnitRuleOptionsException;
use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class EqualRule extends UnitRule implements UnitRuleInterface
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'equal';

    /**
     * {@inheritDoc}
     */
    public function beforeValidating()
    {
        if (empty($this->options[0])) {
            throw new MissingUnitRuleOptionsException('equal rule parameter is missing.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        $value = $this->options[0];

        if ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        } elseif (is_numeric($value)) {
            $value = (float) $value;
        }

        $this->assertingValue = $value;

        return $this->value === $this->assertingValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrorMessage(): string
    {
        $mapValue = function ($value) {
            if ($value === true) return 'true';
            if ($value === false) return 'false';

            return (string) $value;
        };

        $assertingValueType = gettype($this->assertingValue);

        $returnedValueType = gettype($this->value);

        return sprintf(
            ':key must be ' . ($assertingValueType === 'string' ? '"%s"' : '%s') . ', ' . ($returnedValueType === 'string' ? '"%s"' : '%s') . ' returned.',
            $this->color($mapValue($this->assertingValue), 'green'),
            $this->color($mapValue($this->value), 'red')
        );
    }
}
