<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Contracts\Testing\UnitRule;
use HZ\Illuminate\Mongez\Managers\Testing\UnitRuleManager;

class IsRequired extends UnitRuleManager implements UnitRule
{
    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return 'isRequired';
    }

    /**
     * Determine if the rule is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid(mixed $value): bool
    {
        $isRequired = empty($this->options);

        if (! empty($this->options)) {
            $isRequired = $this->options[0];
        }

        return $isRequired ? ! empty($value) : true;
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
