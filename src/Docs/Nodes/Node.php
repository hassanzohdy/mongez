<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Docs\Nodes;

use Exception;

class Node
{
    /**
     * Node Name
     */
    public string $name = '';

    /**
     * Node description
     */
    public string $description = '';

    /**
     * Required State
     */
    public bool $required = true;

    /**
     * Presentable State
     */
    public bool $presentable = true;

    /**
     * Nullable State
     */
    public bool $nullable = false;

    /**
     * Can Be Empty State
     */
    public bool $canBeEmpty = false;

    /**
     * Node Type
     */
    public string $type = '';

    /**
     * Options Values
     */
    public array $options = [];

    /**
     * Default Value
     * 
     * @var mixed
     */
    public $defaultValue = null;

    /**
     * Node Constructor
     *
     * @param  string $name
     * @param  string $description
     */
    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Update required state
     * 
     * @param  bool $required
     * @return self
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Update presentable state
     * 
     * @param  bool $presentable
     * @return self
     */
    public function presentable(bool $presentable = true): self
    {
        $this->presentable = $presentable;

        return $this;
    }

    /**
     * Can Be Empty State
     * 
     * @param  bool $canBeEmpty
     */
    public function canBeEmpty(bool $canBeEmpty = true): self
    {
        $this->canBeEmpty = $canBeEmpty;

        return $this;
    }

    /**
     * Set Node Type
     * 
     * @param  string $type
     * @return self
     */
    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Update nullable state
     * 
     * @param  bool $nullable
     * @return self
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Update options values
     * 
     * @param  array $options
     * @return self
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Update default value
     * 
     * @param  mixed $defaultValue
     * @return self
     */
    public function defaultValue($defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get Node Name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get Node Description
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get Required State
     * 
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get Presentable State
     * 
     * @return bool
     */
    public function isPresentable(): bool
    {
        return $this->presentable;
    }

    /**
     * Get Nullable State
     * 
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Get Can Be Empty State
     * 
     * @return bool
     */
    public function isCanBeEmpty(): bool
    {
        return $this->canBeEmpty;
    }

    /**
     * Get Node Type
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get Options Values
     * 
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get Default Value
     * 
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Get Node
     * 
     * @return array
     */
    public function getNode(): array
    {
        if (!$this->type) {
            throw new Exception(sprintf('Node %s has no type', static::class));
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'required' => $this->required,
            'presentable' => $this->presentable,
            'nullable' => $this->nullable,
            'canBeEmpty' => $this->canBeEmpty,
            'type' => $this->type,
            'options' => $this->options,
            'defaultValue' => $this->defaultValue,
        ];
    }
}
