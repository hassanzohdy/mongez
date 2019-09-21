<?php
namespace HZ\Illuminate\Mongez\Traits;

use App;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $repository
     * @return \HZ\Illuminate\Mongez\Contracts\RepositoryInterface 
     */
    public function __get(string $repository)
    {
        return repo($repository);
    }
}
