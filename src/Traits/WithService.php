<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Traits;

use Illuminate\Support\Str;

trait WithService
{
    /**
     * {@inheritDoc}
     */
    public function __get($key)
    {
        if (Str::endsWith($key, 'Service')) {
            return app()->make(Str::studly($key));
        }
    }
}
