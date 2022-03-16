<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

interface UnitRuleInterface
{
    /**
     * Determine whether the rule will be executed.
     * 
     * @param  bool $executable
     * @return UnitRuleInterface
     */
    public function executable(bool $executable): UnitRuleInterface;

    /**
     * Get rule name, it will be used to be dynamically called from the unit type.
     * 
     * @return string
     */
    public function name(): string;

    /**
     * Get rule message attributes
     * 
     * @return array
     */
    public function getMessageAttributes(): array;

    /**
     * Rule options that can be passe
     * 
     * @param array $options
     * @return UnitRuleInterface
     */
    public function setOptions(array $options): UnitRuleInterface;

    /**
     * Called before calling the rule validation so the rule can check its own requirements first
     * 
     * @return void
     */
    public function beforeValidating();

    /**
     * Determine if the rule is valid
     * 
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Set the unit key
     * 
     * @param  string $key
     * @return UnitRuleInterface
     */
    public function setKey(string $key): UnitRuleInterface;

    /**
     * Set the unit value
     * 
     * @param  mixed $value
     * @return UnitRuleInterface
     */
    public function setValue($value): UnitRuleInterface;

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string;
}
