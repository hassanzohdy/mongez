<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

abstract class AdminApiTestCase extends ApiTestCase
{
    /**
     * If marked as true, a bearer token will be passed with Bearer in the Authorization Header
     * 
     * @var bool
     */
    protected $isAuthenticated = true;

    /**
     * Add Prefix to all routes
     * 
     * @var string
     */
    protected $apiPrefix = '/api/admin';

    /**
     * Module route
     * 
     * @var string
     */
    protected string $route = '';

    /**
     * Response Object
     * 
     * @var \Illuminate\Testing\TestResponse
     */
    protected $response;

    /**
     * Get full data but replace the given array keys
     * 
     * @param array $newData
     * @return array
     */
    protected function fullDataReplace(array $newData): array
    {
        return $this->fullDataWith($newData);
    }

    /**
     * Get full data except the given keys
     * 
     * @param array $exceptKeys
     * @return array
     */
    protected function fullDataExcept(array $exceptKeys): array
    {
        return collect($this->fullData())->except($exceptKeys)->toArray();
    }

    /**
     * Merge the given array with the full data
     * 
     * @param array $otherData
     * @return array
     */
    protected function fullDataWith(array $otherData): array
    {
        return array_merge($this->fullData(), $otherData);
    }

    /**
     * Get request route
     * 
     * @return string
     */
    protected function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Define the full data that should be fully valid.
     * This includes required and optional data
     * 
     * @return array
     */
    abstract protected function fullData(): array;

    /**
     * Define the record shape that will be returned
     * It must contain the entire record shape even if not present in all requests
     * 
     * @return array
     */
    abstract protected function recordShape(): array;
}
