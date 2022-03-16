<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Testing;

use HZ\Illuminate\Mongez\Testing\Traits\Messageable;

class ErrorsMessagesParser
{
    use Messageable;

    /**
     * Errors list
     * 
     * @return array
     */
    protected array $errors;

    /**
     * Constructor
     * 
     * @param  array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Parse the errors and return a clean message
     * 
     * @return array
     */
    public function parse(): array
    {
        $messages = [];

        foreach ($this->errors as $error) {
            $keys = array_map(function ($key) {
                return ':' . $key;
            }, array_keys($error['messageAttributes']));

            $values = array_map(function ($value) {
                return $this->color((string) $value, 'green', ['bold']);
            }, array_values($error['messageAttributes']));

            $messages[] = $this->color($error['rule'], 'magenta') . ': ' .  str_replace(
                $keys,
                $values,
                $error['message']
            );
        }

        return $messages;
    }
}
