<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsEmailRule;

class EmailUnit extends StringUnit
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'email';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->addRules([
            new IsEmailRule(),
        ]);
    }
}
