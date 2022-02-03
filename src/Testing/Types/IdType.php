<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Testing\Rules\Min;

class IdType extends IntType
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->addRule([
            new Min(),
        ]);

        $this->min(1);
    }
}
