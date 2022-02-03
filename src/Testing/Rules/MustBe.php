<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use Exception;
use HZ\Illuminate\Mongez\Contracts\Testing\UnitRule;
use HZ\Illuminate\Mongez\Managers\Testing\UnitRuleManager;

class MustBe extends UnitRuleManager implements UnitRule
{
    /**
     * Asserting value
     */
    protected $assertingValue;

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return 'mustBe';
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        if (empty($this->options[0])) {
            throw new Exception('mustBe method parameter is missing.');
        }

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

        return $this->message(
            sprintf(
                'must be ' . ($assertingValueType === 'string' ? '"%s"' : '%s') . ', ' . ($returnedValueType === 'string' ? '"%s"' : '%s') . ' returned.',
                $this->color($mapValue($this->assertingValue), 'green'),
                $this->color($mapValue($this->value), 'red')
            )
        );
    }
}
