<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Nodes;

use Exception;

class ParamNode extends Node
{
    /**
     * Required State
     */
    public bool $required = false;

    /**
     * {@inheritDoc}
     */
    public function getNode(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            'type' => $this->type,
            'options' => $this->options,
            'defaultValue' => $this->defaultValue,
        ];
    }
}
