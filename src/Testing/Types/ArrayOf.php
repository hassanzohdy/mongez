<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Testing\Rules\IsArray;
use HZ\Illuminate\Mongez\Managers\Testing\UnitType;

class ArrayOf extends UnitType
{
    /**
     * The unit type that should be contained in the array
     * 
     * @var unitTypeClass
     */
    protected string $unitTypeClass;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $key, string $unitTypeClass)
    {
        parent::__construct($key);

        $this->unitTypeClass = $unitTypeClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRule([
            new IsArray(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(mixed $value): bool
    {
        $isValid = parent::isValid($value);

        if ($isValid === false) return false;

        $class = $this->unitTypeClass;

        foreach ($value as $index => $singleValue) {
            $unitType = new $class($this->key . '.' . $index);

            if ($unitType->valid($singleValue) === false) {
                $this->errorsList = array_merge($this->errorsList, $unitType->errorsList());
            }
        }

        return empty($this->errorsList);
    }
}
