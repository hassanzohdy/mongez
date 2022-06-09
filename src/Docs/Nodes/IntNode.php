<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Nodes;

class IntNode extends Node
{
    /**
     * {@inheritDoc}
     */
    public string $type = 'int';

    /**
     * {@inheritDoc}
     */
    public $defaultValue = 0;
}
