<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Rules;

use HZ\Illuminate\Mongez\Testing\Traits\Messageable;
use HZ\Illuminate\Mongez\Testing\Traits\WithKeyAndValue;
use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;
use HZ\Illuminate\Mongez\Testing\Units\UnitType;

abstract class UnitRule implements UnitRuleInterface
{
    use WithKeyAndValue;
    use Messageable;

    /**
     * Rule name
     * 
     * @const string
     */
    const NAME = '';

    /**
     * The unit that holds the rule
     * 
     * @var UnitType
     */
    protected $unit;


    /**
     * Rule Error Message
     * 
     * @var string 
     */
    protected string $errorMessage = '';

    /**
     * Rule options
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Set rule unit
     * 
     * @var  UnitType $unit
     * @return self
     */
    public function setUnit(UnitType $unit): UnitRule
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * Determine whether the rule will be executed.
     * 
     * @param  bool $executable
     * @return UnitRule
     */
    public function executable(bool $executable): UnitRuleInterface
    {
        return $this;
    }

    /**
     * Called before calling the rule validation so the rule can check its own requirements first
     * 
     * @return void
     */
    public function beforeValidating()
    {
    }

    /**
     * Rule options that can be passe
     * 
     * @param array $options
     * @return UnitRuleInterface
     */
    public function setOptions(array $options): UnitRuleInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Determine if the rule is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Get error message
     * 
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * {@inheritDoc}
     */
    public function name(): string
    {
        return static::NAME;
    }

    /**
     * Get rule message attributes
     * 
     * @return array
     */
    public function getMessageAttributes(): array
    {
        return [];
    }
}
