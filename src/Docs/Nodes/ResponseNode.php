<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Nodes;


class ResponseNode extends Node
{
    /**
     * Response data
     * 
     * Used With objects and arrays
     */
    public array $data = [];

    /**
     * Set Response data
     * 
     * @param array $data
     * @return ResponseNode
     */
    public function setData(array $data): ResponseNode
    {
        $this->data = $data;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNode(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'presentable' => $this->presentable,
            'nullable' => $this->nullable,
            'canBeEmpty' => $this->canBeEmpty,
            'type' => $this->type,
            'options' => $this->options,
            'defaultValue' => $this->defaultValue,
            'data' => $this->data, // objects or arrays
        ];
    }
}
