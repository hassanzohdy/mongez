<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Managers\Testing;

use HZ\Illuminate\Mongez\Contracts\Testing\UnitRule;

abstract class UnitRuleManager implements UnitRule
{
    /**
     * Determine if the rule will be executable
     * 
     * @var bool
     */
    protected bool $executable;

    /**
     * Unit Key name
     * 
     * @var string
     */
    protected string $key;

    /**
     * Rule options list
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Determine whether the rule will be executed.
     * 
     * @param  bool $executable
     * @return UnitRule
     */
    public function executable(bool $executable): UnitRule
    {
        $this->executable = $executable;

        return $this;
    }

    /**
     * Set the unit key
     * 
     * @param  string $key
     * @return UnitRule
     */
    public function setKey(string $key): UnitRule
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Rule options that can be passe
     * 
     * @param array $options
     * @return UnitRule
     */
    public function options(...$options): UnitRule
    {
        $this->options = $options;

        return $this;
    }
}
