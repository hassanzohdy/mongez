<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

interface ResponseSchemaInterface
{
    /**
     * A flag to determine if the unit key is missing from the response
     * 
     * @const string
     */
    const MISSING_RESPONSE_KEY = '__MISSING__KEY__';

    /**
     * Validate the response
     * 
     * @return self
     */
    public function validate();

    /**
     * Determine if the response schema must be strict
     * 
     * @param  bool $isStrict
     * @return  ResponseSchemaInterface
     */
    public function strict(bool $isStrict);

    /**
     * Determine if the response is valid
     * 
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get errors list
     * 
     * @return array
     */
    public function errorsList(): array;
}
