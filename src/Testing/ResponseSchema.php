<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Testing\Units\ObjectUnit;

class ResponseSchema extends ObjectUnit implements ResponseSchemaInterface
{
    /**
     * Constructor
     * @param array $unitsList
     */
    public function __construct(array $unitsList = [])
    {
        $rootKey = config('mongez.testing.response.rootKey');

        if ($rootKey) {
            $unitsList = [
                $rootKey => new ObjectUnit($unitsList),
            ];
        }

        parent::__construct($unitsList);
    }
}
