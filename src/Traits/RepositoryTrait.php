<?php

namespace HZ\Illuminate\Mongez\Traits;

use Illuminate\Support\Str;

trait RepositoryTrait
{
    /**
     * Get repositories dynamically
     *
     * @param string $key
     * @return \HZ\Illuminate\Mongez\Contracts\RepositoryInterface|mixed 
     */
    public function __get($key)
    {
        $repository = null;

        if (Str::endsWith($key, 'Repository')) {
            $repository = Str::replaceLast('Repository', '', $key);
        } else if (Str::endsWith($key, 'Repo')) {
            $repository = Str::replaceLast('Repo', '', $key);
        }

        if ($repository) {
            return repo($repository);
        }

        if (method_exists(parent::class, '__get')) {
            return parent::__get($key);
        }
    }
}