<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Contracts\Testing;

interface ResponseSchemaInterface
{
    /**
     * Determine if the response is valid
     * 
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value): bool;

    /**
     * Get errors list
     * 
     * @return array
     */
    public function errorsList(): array;
}
