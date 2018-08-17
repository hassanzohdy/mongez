<?php
namespace HZ\Laravel\Organizer\App\Traits;

use App;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $repository
     * @return \HZ\Laravel\Organizer\App\Contracts\RepositoryInterface 
     */
    public function __get(string $repository)
    {
        return repo($repository);
    }
}
