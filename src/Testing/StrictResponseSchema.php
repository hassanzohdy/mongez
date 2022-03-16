<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

class StrictResponseSchema extends ResponseSchema implements ResponseSchemaInterface
{
    /**
     * {@inheritDoc}
     */
    protected $isStrict = true;
}
