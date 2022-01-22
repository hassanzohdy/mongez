<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Managers\Testing\UnitType;
use HZ\Illuminate\Mongez\Testing\Rules\IsObject;

class ObjectType extends UnitType
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->addRule([
            new IsObject(),
        ]);
    }
}
