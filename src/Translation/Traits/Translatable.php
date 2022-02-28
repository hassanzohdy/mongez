<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Translation\Traits;

use Illuminate\Support\Str;

trait Translatable
{
    /**
     * Translate message from modules dynamically
     */
    public function __call($method, $args)
    {
        if (!Str::startsWith($method, 'trans')) {
            if (method_exists(get_parent_class($this), '__call')) {
                return call_user_func_array([get_parent_class($this), '__call'], $args);
            }

            return;
        }

        $moduleName = Str::removeFirst('trans', $method);

        $fileName = array_shift($args);

        return trans($moduleName . '::' . $fileName, ...$args);
    }
}
