<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing\Traits;

use Symfony\Component\Console\Color;

trait Messageable
{
    /**
     * Get colored key
     * 
     * @return string
     */
    public function color(string $text, string $color = '', array $options = []): string
    {
        return (new Color($color, '', $options))->apply($text);
    }

    /**
     * Get a message to be displayed
     * 
     * @param  string $message
     * @param  string $color
     * @return string
     */
    protected function message(string $message, string $color = ''): string
    {
        return $this->color($message, $color);
    }

    /**
     * Print the given message
     * 
     * @param string $message
     * @return void
     */
    protected function instantMessage(string $message, string $color = '')
    {
        if ($color) {
            $message = $this->color($message, $color);
        }

        echo $message . PHP_EOL;
    }
}
