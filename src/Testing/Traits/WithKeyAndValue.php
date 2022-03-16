<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Traits;

trait WithKeyAndValue
{
    /**
     * Unit Value
     * 
     * @var mixed
     */
    protected $value;

    /**
     * Unit Key
     * 
     * @var string
     */
    protected string $key = '';

    /**
     * Unit parent key
     * 
     * @var string
     */
    protected string $parentKey = '';

    /**
     * Key full namespace
     */
    protected string $keyNamespace = '';

    /**
     * Set unit key namespace
     * 
     * @param  string $keyNamespace
     * @return self
     */
    public function setKeyNamespace(string $keyNamespace): self
    {
        $this->keyNamespace = $keyNamespace;

        return $this;
    }

    /**
     * Set unit parent key
     * 
     * @param  string $parentKey
     * @return self
     */
    public function setParentKey(string $parentKey): self
    {
        $this->parentKey = $parentKey;

        return $this;
    }

    /**
     * Set unit value
     * 
     * @param mixed $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Set unit key
     * 
     * @param string $key
     * @return self
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get full key path which is the parent key concated with current key
     * 
     * @return string
     */
    public function fullKeyPath(): string
    {
        return ($this->keyNamespace ? $this->keyNamespace . '.' : '') . $this->key;
    }

    /**
     * Get error prefixed with the full key name
     * 
     * @param  string $error
     * @return string
     */
    public function keyError(string $error)
    {
        return '`' . $this->fullKeyPath() . '` key ' . $error;
    }
}
