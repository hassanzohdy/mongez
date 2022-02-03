<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Contracts\Testing\UnitRule;
use HZ\Illuminate\Mongez\Managers\Testing\UnitRuleManager;

class Nullable extends UnitRuleManager implements UnitRule
{
    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return 'nullable';
    }

    /**
     * Determine if the rule is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid(mixed $value): bool
    {
        if (! empty($value)) return true;

        $isNullable = empty($this->options);

        if (! empty($this->options)) {
            $isNullable = $this->options[0];
        }

        return $isNullable ? $value === null : true;
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->key . ' can be only null with its original type';
    }
}
