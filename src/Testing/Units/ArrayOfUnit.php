<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use Closure;

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
     * Perform operation on each unit class in the array list
     * 
     * @var rarray
     */
    private array $callbacks = [];

    /**
     * Append a callback to each unit type
     * 
     * @param  \Clouse $callback
     * @return self
     */
    public function each(Closure $callback): ArrayOfUnit
    {
        $this->callbacks[] = $callback;
        return $this;
    }

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

            $unitType->setKeyNamespace($this->fullKeyPath())
                ->setParentKey($this->key)
                ->setKey((string) $index);
            $unitType->setValue($singleValue);

            if ($this->hasDeterminedIfStrict() && !$unitType->hasDeterminedIfStrict()) {
                $unitType->strict($this->isStrict);
            }


            foreach ($this->callbacks as $callback) {
                $callback($unitType, $index, $class);
            }

            $unitType->beforeValidation();

            $unitType->validate();

            if ($unitType->isValid() === false) {
                $this->errorsList = array_merge($this->errorsList, $unitType->errorsList());
            }
        }

        return $this;
    }
}
