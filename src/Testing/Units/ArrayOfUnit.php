<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;


class ArrayOfUnit extends ArrayUnit
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'arrayOf';

    /**
     * The unit type that should be contained in the array
     * 
     * @var unitTypeClass
     */
    protected string $unitTypeClass;

    /**
     * {@inheritDoc}
     */
    public function __construct(string $unitTypeClass)
    {
        parent::__construct();
        $this->unitTypeClass = $unitTypeClass;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): self
    {
        parent::validate();

        if ($this->isValid() === false) return $this;

        $class = $this->unitTypeClass;

        foreach ($this->value as $index => $singleValue) {
            $unitType = new $class();

            $unitType->setKeyNamespace($this->fullKeyPath())->setParentKey($this->key)->setKey((string) $index);
            $unitType->setValue($singleValue);

            if ($this->hasDeterminedIfStrict() && !$unitType->hasDeterminedIfStrict()) {
                $unitType->strict($this->isStrict);
            }

            $unitType->validate();

            if ($unitType->isValid() === false) {
                $this->errorsList = array_merge($this->errorsList, $unitType->errorsList());
            }
        }

        return $this;
    }
}
