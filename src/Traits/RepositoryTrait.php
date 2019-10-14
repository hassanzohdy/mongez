<?php
namespace HZ\Illuminate\Mongez\Traits;

use App;
use Illuminate\Support\Str;

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
        if (Str::endsWith($repository, 'Repository')) {
            $repository = Str::replaceLast('Repository', '', $repository);
        } else if (Str::endsWith($repository, 'Repo')) {            
            $repository = Str::replaceLast('Repo', '', $repository);
        }

        return repo($repository);
    }
}
