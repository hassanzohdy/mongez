<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Managers\Testing;

use HZ\Illuminate\Mongez\Testing\Rules\CanBeEmpty;
use HZ\Illuminate\Mongez\Testing\Rules\IsRequired;
use HZ\Illuminate\Mongez\Testing\Rules\Nullable;
use Illuminate\Support\Str;

abstract class UnitType
{
    /**
     * Unit value
     * 
     * @var mixed
     */
    protected mixed $value;

    /**
     * Unit key
     * 
     * @var string
     */
    protected string $key;

    /**
     * Unit existing type
     * 
     * @var bool
     */
    protected bool $isRequired = true;

    /**
     * Unit can be nullable
     * 
     * @var bool
     */
    protected bool $isNullable = false;

    /**
     * Validation Rules list
     * 
     * @var array
     */
    protected array $rulesList = [];

    /**
     * Errors list
     * 
     * @var array
     */
    protected array $errorsList = [];

    /**
     * Unit can be empty
     * 
     * @var bool
     */
    protected bool $canBeEmpty = false;

    /**
     * Constructor
     * 
     * @param  string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;

        $this->addRule([
            new Nullable(),
            new CanBeEmpty(),
            new IsRequired(),
        ]);

        $this->init();
    }

    /**
     * Initialize the class
     * 
     * @return void
     */
    protected abstract function init();

    /**
     * Add one or more rule to rhe unit rules list
     * 
     * @param  InputRule|InputRule[] $rules
     * @return self
     */
    public function addRule($rules)
    {
        if (is_array($rules)) {
            $this->rulesList = array_merge($this->rulesList, $rules);
        } else {
            $this->rulesList[] = $rules;
        }

        return $this;
    }

    /**
     * Validate the unit
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        $this->value = $value;

        foreach ($this->rulesList as $rule) {
            if ($rule->executable() === false) continue;

            if ($rule->setKey($this->key)->isValid($value) === false) {
                $this->errorsList[] = $rule->getErrorMessage();
            }
        }

        return ! empty($this->errorsList);
    }

    /**
     * Get input errors list
     * 
     * @return array of strings
     */
    public function errorsList(): array
    {
        return $this->errorsList;
    }

    /**
     * Call the rule dynamically to be executed
     * 
     * @param  string $ruleName
     * @param  array $args
     * @return mixed
     */
    public function __call(string $ruleName, array $args): self
    {
        $executable = true;

        if (Str::startsWith($ruleName, 'not')) {
            $ruleName = ltrim($ruleName, 'not');
            $executable = false;
        }

        $ruleName = Str::camel($ruleName);

        foreach ($this->rules as $rule) {
            if ($rule->name() === $ruleName) {
                $rule->executable($executable)->options(...$args);
            }
        }

        return $this;
    }
}