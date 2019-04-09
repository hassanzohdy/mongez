<?php
namespace HZ\Illuminate\Organizer\Traits;

use App;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $repository
     * @return \HZ\Illuminate\Organizer\Contracts\RepositoryInterface 
     */
    public function __get(string $repository)
    {
        return repo($repository);
    }
}
