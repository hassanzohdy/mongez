<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\InvalidUnitTypeException;
use Illuminate\Support\Arr;
use HZ\Illuminate\Mongez\Testing\Rules\IsObjectRule;
use HZ\Illuminate\Mongez\Testing\ResponseSchemaInterface;

class ObjectUnit extends UnitType
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'object';

    /**
     * Unit innner units list
     * 
     * @var array
     */
    protected array $unitsList = [];

    /**
     * Constructor
     * @param array $unitsList
     */
    public function __construct(array $unitsList = [])
    {
        $rootKey = config('mongez.testing.response.rootKey');
        if ($rootKey) {
            foreach ($unitsList as $key => $unit) {
                unset($unitsList[$key]);
                $unitsList[$rootKey . '.' . $key] = $unit;
            }
        }

        $this->setUnits($unitsList);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRules([
            new IsObjectRule(),
        ]);
    }

    /**
     * Set units list
     * 
     * @param  array $unitsList
     * @return UnitType
     */
    public function setUnits($unitsList): UnitType
    {
        $this->unitsList = $unitsList;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(): self
    {
        parent::validate();

        if (!$this->isValid()) return $this;

        // TODO: Strict with additional keys that should not be in the response
        if ($this->isStrict) {
            $additionalKeys = array_diff(
                array_keys($this->value),
                array_keys($this->unitsList),
            );

            if ($additionalKeys) {
                return $this->addError('strict', sprintf(':key has addiotional keys that is is not supposed to be in the response, %s', $this->color(implode(', ', $additionalKeys), 'yellow')));
            }
        }

        foreach ($this->unitsList as $key => $unitType) {
            $unitValue = Arr::get($this->value, $key, ResponseSchemaInterface::MISSING_RESPONSE_KEY);

            if (is_array($unitType)) {
                $unitTypeName = array_shift($unitType);
                $unitMethods = $unitType;
                $unit = $this->getUnit($unitTypeName);

                foreach ($unitMethods as $method) {
                    $methodWithOptions = explode(':', $method);
                    $methodName = array_shift($methodWithOptions);

                    $unit->$methodName(...$methodWithOptions);
                }

                $unitType = $unit;
            }

            if (is_string($unitType)) {
                $unit = $this->getUnit($unitType);
            } else {
                $unit = $unitType;
            }

            $unit->setKeyNamespace($this->fullKeyPath());
            $unit->setParentKey($this->key);
            $unit->setKey($key);
            $unit->setValue($unitValue);
            if ($this->hasDeterminedIfStrict() && !$unit->hasDeterminedIfStrict()) {
                $unit->strict($this->isStrict);
            }

            $unit->validate();

            if (!$unit->isValid()) {
                $this->errorsList = array_merge($this->errorsList, $unit->errorsList());
            }
        }

        return $this;
    }

    /**
     * Get an instance of the given unit type
     * 
     * The given unit type can be a class of unit or a an alias listed in mongez.testing.units
     * 
     * @param  string $unitName
     * @return UnitType
     * @throws InvalidUnitTypeException
     */
    protected function getUnit(string $unitName): UnitType
    {
        if (class_exists($unitName)) {
            $unit = new $unitName();
        } elseif (static::isListedUnit($unitName)) {
            $unit = static::resolveUnit($unitName);
        } else {
            throw new InvalidUnitTypeException(sprintf('%s Unit is not listed in the mongez.testing.units list, please define it there first', $this->color($unitName, 'cyan')));
        }

        return $unit;
    }
}
