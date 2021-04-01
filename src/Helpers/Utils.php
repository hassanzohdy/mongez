<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Helpers;

class Utils
{
    /**
     * Get current app type
     * 
     * @return string
     */
    public static function appType(): string
    {
        return config('app.type');
    }
}