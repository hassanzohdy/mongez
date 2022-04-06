<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Http;

use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Http\ApiResponse;
use HZ\Illuminate\Mongez\Traits\WithRepositoryAndService;
use HZ\Illuminate\Mongez\Translation\Traits\Translatable;

abstract class ApiController
{
    use ApiResponse, Translatable, WithRepositoryAndService;

    /**
     * Repository name
     * If provided, then the repository property will be the object of the repository
     * 
     * @const string
     */
    public const REPOSITORY_NAME = '';

    /**
     * Service Class 
     * 
     * @const string
     */
    public const SERVICE_CLASS = '';

    /**
     * Repository Object
     * Can be filled when REPOSITORY_NAME is provided.
     * 
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Service object
     * 
     * @var mixed
     */
    protected $service;

    /**
     * Events Object
     *
     * @var Events
     */
    protected $events;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->events = app()->make(Events::class);

        if (static::REPOSITORY_NAME) {
            $this->repository = repo(static::REPOSITORY_NAME);
        }

        if (static::SERVICE_CLASS) {
            $this->service = app()->make(static::SERVICE_CLASS);
        }
    }
}
