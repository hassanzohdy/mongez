<?php

namespace HZ\Illuminate\Mongez\Http;

use HZ\Illuminate\Mongez\Events\Events;
use HZ\Illuminate\Mongez\Http\ApiResponse;
use HZ\Illuminate\Mongez\Repository\Concerns\RepositoryTrait;

abstract class ApiController
{
    use RepositoryTrait, ApiResponse;

    /**
     * Repository name
     * If provided, then the repository property will be the object of the repository
     * 
     * @const string
     */
    public const REPOSITORY_NAME = '';

    /**
     * Repository Object
     * Can be filled when REPOSITORY_NAME is provided.
     * 
     * @var RepositoryInterface
     */
    protected $repository;

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
    public function __construct(Events $events)
    {
        $this->events = $events;

        if (static::REPOSITORY_NAME) {
            $this->repository = repo(static::REPOSITORY_NAME);
        }
    }
}
