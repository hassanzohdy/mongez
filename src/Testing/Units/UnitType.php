<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\InvalidUnitRuleException;
use HZ\Illuminate\Mongez\Testing\ResponseSchemaInterface;
use HZ\Illuminate\Mongez\Testing\Traits\Messageable;
use HZ\Illuminate\Mongez\Testing\Traits\StrictUnit;
use HZ\Illuminate\Mongez\Testing\Traits\WithKeyAndValue;
use HZ\Illuminate\Mongez\Testing\UnitRuleInterface;

class UnitType
{
    use StrictUnit;
    use WithKeyAndValue;
    use Messageable;

    /**
     * Unit type name
     * 
     * @const string
     */
    const NAME = '';

    /**
     * Errors List
     * 
     * @var array
     */
    protected array $errorsList = [];

    /**
     * A flag to determine if the unit key is missing from the response
     * 
     * @var bool
     */
    protected bool $isMissingKey = false;

    /**
     * Rules list
     */
    protected array $rules = [];

    /**
     * Determine if unit can be empty
     * 
     * @var bool
     */
    protected bool $canBeEmpty = false;

    /**
     * Determine if unit can be null
     * 
     * @var bool
     */
    protected bool $isNulable = false;

    /**
     * Rules options
     * 
     * @var array
     */
    protected array $rulesOptions = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Called before validation
     * 
     * @return void
     */
    public function beforeValidation()
    {
    }

    /**
     * Unit can have empty value
     * 
     * @return UnitType
     */
    public function canBeEmpty(): UnitType
    {
        $this->canBeEmpty = true;

        return $this;
    }

    /**
     * Unit with null value will be marked as invalid
     * 
     * @return UnitType
     */
    public function canNotBeEmpty(): UnitType
    {
        $this->canBeEmpty = false;

        return $this;
    }

    /**
     * Unit with null value will be marked as valid
     * 
     * @return UnitType
     */
    public function nullable(): UnitType
    {
        $this->isNulable = true;

        return $this;
    }

    /**
     * Unit with null value will be marked as invalid
     * 
     * @return UnitType
     */
    public function notNullable(): UnitType
    {
        $this->isNulable = false;

        return $this;
    }

    /**
     * Get unit type name
     * 
     * @return string
     */
    protected function name(): string
    {
        return static::NAME;
    }

    /**
     * Initialize the unit
     * 
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Add ruels to the current unit
     * 
     * @param  UnitRule[] $rules
     * @return $this
     */
    public function addRules(array $rules): UnitType
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    /**
     * Mark the current unit as a missing key
     * 
     * @return UnitType
     */
    public function missingKey(): UnitType
    {
        $this->isMissingKey = true;
        return $this;
    }

    /**
     * Validate the unit
     * 
     * @return self
     */
    public function validate(): self
    {
        if ($this->isStrict && $this->value === ResponseSchemaInterface::MISSING_RESPONSE_KEY) {
            return $this->addError('missingKey', ':key key is missing from response');
        }

        if ($this->isNulable && $this->value === null) {
            return $this;
        }

        if ($this->isNulable === false && $this->value === null) {
            return $this->addError('nullable', ':key key can not be null');
        }

        if ($this->canBeEmpty === false && empty($this->value) && !in_array($this->value, [0, '0'])) {
            return $this->addError('empty',  ':key can not empty');
        }

        foreach ($this->rules as $rule) {
            $rule->setParentKey($this->key)->setKeyNamespace($this->fullKeyPath())->setKey($this->key)->setValue($this->value);

            $rule->setOptions($this->getRuleOptions($rule));

            $rule->setUnit($this);

            $rule->beforeValidating();

            if (!$rule->isValid()) {
                $this->addError($rule->name(), $rule->getErrorMessage(), $rule->getMessageAttributes());
            }
        }

        return $this;
    }

    /**
     * Get rule options
     * 
     * @param UnitRuleInterface $rule
     * @return array
     */
    public function getRuleOptions(UnitRuleInterface $rule): array
    {
        return $this->rulesOptions[$rule->name()] ?? [];
    }

    /**
     * Add error to errors list
     * 
     * @param  string $ruleError
     * @param  string $errorMessage
     * @param  array $messageAttributes
     * @return UnitType
     */
    public function addError(string $ruleError, string $errorMessage, array $messageAttributes = []): UnitType
    {
        $this->errorsList[] = [
            'rule' => $ruleError,
            'message' => $errorMessage,
            'key' => $this->fullKeyPath(),
            'parentKey' => $this->parentKey,
            'singleKey' => $this->key,
            'messageAttributes' => array_merge($this->getBaseUnitMessageAttributes(), $messageAttributes),
        ];

        return $this;
    }

    /**
     * Get base message attributes 
     * 
     * @return array
     */
    public function getBaseUnitMessageAttributes(): array
    {
        if ($this->value === ResponseSchemaInterface::MISSING_RESPONSE_KEY) {
            $type = 'none';
        } else {
            $type = gettype($this->value);
        }

        if ($type === 'array' && is_object((object) $this->value)) {
            $type = 'object';
        }

        return [
            'unit' => $this->name(),
            'singleKey' => $this->key,
            'parentKey' => $this->parentKey,
            'key' => $this->fullKeyPath(),
            'valueType' => $type,
            'value' => json_encode($this->value),
        ];
    }

    /**
     * Check if the unit valid
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errorsList);
    }

    /**
     * Set Rules options, also add additional rules on the fly if not exist in the unit rule
     */
    public function __call($rule, $ruleOptions)
    {
        if (!$this->hasRule($rule)) {
            if (static::isListedRule($rule)) {

                $this->addRules([
                    static::resolveRule($rule),
                ]);
            } else {
                throw new InvalidUnitRuleException(sprintf('The current unit %s does not have %s rule, also there are no rule called %s in the rules list', $this->color($this->name(), 'magenta'),  $ruleName = $this->color($rule, 'cyan'), $ruleName));
            }
        }

        $this->rulesOptions[$rule] = $ruleOptions;

        return $this;
    }

    /**
     * Check if the given rule is listed in the rules list
     * 
     * @param  string $rule
     * @return bool
     */
    public static function isListedRule(string $rule): bool
    {
        return (bool) config("mongez.testing.rules.$rule");
    }

    /**
     * Resolve the given rule class 
     * 
     * @param  string $rule
     * @return UnitRuleInterface
     */
    public static function resolveRule(string $rule): UnitRuleInterface
    {
        $unitRule = config("mongez.testing.rules.$rule");
        return new $unitRule;
    }

    /**
     * Check if the given unit is listed in the units list
     * 
     * @param  string $unit
     * @return bool
     */
    public static function isListedUnit(string $unit): bool
    {
        return (bool) config("mongez.testing.units.$unit");
    }

    /**
     * Resolve the given unit class 
     * 
     * @param  string $unit
     * @return UnitType
     */
    public static function resolveUnit(string $unit): UnitType
    {
        $unitRule = config("mongez.testing.units.$unit");
        return new $unitRule;
    }

    /**
     * Check if current unit has the given rule name
     * 
     * @param  string $ruleName
     * @return bool
     */
    public function hasRule(string $ruleName): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule->name() === $ruleName) return true;
        }

        return false;
    }

    /**
     * Get Errors List
     * 
     * @return array
     */
    public function errorsList(): array
    {
        return $this->errorsList;
    }
}
