<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\MinRule;

class IdUnit extends IntUnit
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'id';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->addRules([
            new MinRule(),
        ]);

        $this->min(1);
    }
}
