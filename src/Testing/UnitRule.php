<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Contracts\Testing;

interface UnitRule
{
    /**
     * Determine whether the rule will be executed.
     * 
     * @param  bool $executable
     * @return UnitRule
     */
    public function executable(bool $executable): UnitRule;

    /**
     * Get rule name, it will be used to be dynamically called from the unit type.
     * 
     * @return string
     */
    public function name(): string;

    /**
     * Rule options that can be passe
     * 
     * @param array $options
     * @return UnitRule
     */
    public function options(...$options): UnitRule;

    /**
     * Determine if the rule is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value): bool;

    /**
     * Set the unit key
     * 
     * @param  string $key
     * @return UnitRule
     */
    public function setKey(string $key): UnitRule;

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string;
}
