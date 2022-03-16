<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Units;

use HZ\Illuminate\Mongez\Testing\Rules\IsUrlRule;

class UrlUnit extends StringUnit
{
    /**
     * {@inheritDoc}
     */
    const NAME = 'url';

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->addRules([
            new IsUrlRule(),
        ]);
    }
}
