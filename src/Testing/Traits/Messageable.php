<?php
declare(strict_types=1);
namespace HZ\Illuminate\Mongez\Traits\Testing;

use Symfony\Component\Console\Color;
use HZ\Illuminate\Mongez\Helpers\Testing\Message;

trait Messageable
{
    /**
     * Get colored key
     * 
     * @return string
     */
    public function color(string $text, string $color = ''): string
    {
        return (new Color($color))->apply($text);
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
        return (new Message($this->key ?? ''))->apply($message, $color);
    }

    /**
     * Print the given message
     * 
     * @param string $message
     * @param string $color
     * @return void
     */
    protected function instantMessage(string $message, string $color)
    {
        echo $this->message($message, $color) . PHP_EOL;

        echo ob_get_clean();
    }
}
