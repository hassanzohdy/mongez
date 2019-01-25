<?php
namespace HZ\Laravel\Organizer\Traits;

use App;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $repository
     * @return \HZ\Laravel\Organizer\Contracts\RepositoryInterface 
     */
    public function __get(string $repository)
    {
        return repo($repository);
    }
}
