<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

class UserUnit extends ObjectUnit
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->setUnits([
            'id' => (new IdUnit)->equal(53),
            'published' => 'bool',
            'group' => ['bool', 'nullable', 'canBeEmpty'],
            'name' => 'string',
            'email' => 'email',
            'phoneNumber' => 'string',
        ]);
    }
}
