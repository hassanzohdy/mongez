<?php
declare(strict_types=1);
namespace HZ\Illuminate\Mongez\Managers\Testing;

use Illuminate\Support\Str;
use HZ\Illuminate\Mongez\Helpers\Testing\Message;
use HZ\Illuminate\Mongez\Traits\Testing\Messageable;

use HZ\Illuminate\Mongez\Testing\Rules\{
    CanNotBeEmpty,
    MustBe,
    MustBeOneOf
};

abstract class UnitType
{
    use Messageable;

    /**
     * Missing key flag
     * 
     * @const string
     */
    public const MISSING_KEY = '__MISSING__KEY__';

    /**
     * Unit value
     * 
     * @var mixed
     */
    protected $value;

    /**
     * Unit key
     * 
     * @var string
     */
    protected string $key;

    /**
     * A flag to indicate the value is nullable
     * 
     * @var bool
     */
    protected bool $isNullable = false;

    /**
     * A flag to check if the key must be existing in the response schema
     * 
     * @var bool
     */
    protected bool $keyMustExist = true;

    /**
     * Full key namespace
     * 
     * @var string
     */
    protected string $namespace = '';

    /**
     * The key state in the response schema
     * 
     * @var bool
     */
    protected bool $isKeyMissing = false;

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
            new CanNotBeEmpty(),
            new MustBe(),
            new MustBeOneOf()
        ]);

        $this->init();
    }

    /**
     * A flag to indicate the key may be nullable
     * 
     * @return self
     */
    public function nullable(): self
    {
        $this->isNullable = true;

        return $this;
    }

    /**
     * A flag to indicate the key may be nullable
     * 
     * @return self
     */
    public function notNullable(): self
    {
        $this->isNullable = false;

        return $this;
    }

    /**
     * Mark the unit as it can be empty
     * 
     * @return self
     */
    public function canBeEmpty(): self
    {
        return $this->canNotBeEmpty(false);
    }

    /**
     * Determine whether the key is missing
     * 
     * @param  bool $isKeyMissing
     * @return self
     */
    public function isKeyMissing(bool $isKeyMissing): self
    {
        $this->isKeyMissing = $isKeyMissing;

        return $this;
    }

    /**
     * Make sure the key must exist in the response schema
     * 
     * @return self
     */
    public function mustExist(): self
    {
        $this->keyMustExist = true;

        return $this;
    }

    /**
     * The key may not exist in the response schema
     * 
     * @return self
     */
    public function mayBeMissing(): self
    {
        $this->mustExist = false;

        return $this;
    }

    /**
     * Set namespace for the key
     * 
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
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
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if ($this->isKeyMissing && $this->keyMustExist) {
            $this->errorsList[] = (new Message($this->getKeyNamespace()))->apply('key is missing', 'yellow');
            return false;
        } elseif ($this->isKeyMissing && $this->keyMustExist === false) return true;

        $this->value = $value;

        if (is_null($value) && $this->isNullable) return true;

        foreach ($this->rulesList as $rule) {
            if ($rule->isExecutable() === false) continue;

            $rule->setKey($this->getKeyNamespace())->setValue($value);

            if ($rule->isValid() === false) {
                $this->errorsList[] = $rule->getErrorMessage();
            }
        }

        return empty($this->errorsList);
    }

    /**
     * Get the key will its namespace
     * 
     * @return string
     */
    public function getKeyNamespace(): string
    {
        if (!$this->namespace) return $this->key;

        return $this->namespace . '.' . $this->key;
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

        foreach ($this->rulesList as $rule) {
            if ($rule->name() === $ruleName) {
                $rule->executable($executable)->options(...$args);
            }
        }

        return $this;
    }

    /**
     * Get key name
     * 
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
