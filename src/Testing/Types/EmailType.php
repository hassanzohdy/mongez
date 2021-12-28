<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Types;

use HZ\Illuminate\Mongez\Testing\Rules\IsEmail;

class EmailType extends StringType
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();
        $this->addRule([
            new IsEmail(),
        ]);
    }
}
