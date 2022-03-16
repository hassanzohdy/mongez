<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

class ErrorsListUnit extends ArrayOfUnit
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $errorsKeys)
    {
        $units = [];

        foreach ($errorsKeys as $index => $errorKey) {
            $units[$index . '.key'] = ['string', 'equal:' . $errorKey];
            $units[$index . '.value'] = ['string'];
        }

        $this->each(function ($unit, $index) use ($errorsKeys) {
            $unit->setErrorKeyName($errorsKeys[$index]);
        });

        parent::__construct(ErrorKeyValueUnit::class);
    }
}
