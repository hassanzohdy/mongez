<?php
namespace App\Traits;

use App;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $repository
     * @return \App\Contracts\RepositoryInterface 
     */
    public function __get(string $repository)
    {
        return repo($repository);
    }
}
