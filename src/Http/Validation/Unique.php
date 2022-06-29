<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Http\Validation;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Validator;
use Exception;

class Unique
{
    /**
     * @var array
     */
    private array $parameters = ['table', 'column', 'ignoreValue'];

    /**
     * @var string
     */
    private string $attribute;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param $parameters
     * @return bool
     * @throws Exception
     */
    public function passes(string $attribute, $value, $parameters): bool
    {
        $this->validateParameters($parameters);

        $this->attribute = $attribute;

        return !DB::table($this->parameters['table'])->where($this->parameters['column'], $value)->where($this->parameters['ignoreColumn'] ?? 'id', '!=', (int) $this->parameters['ignoreValue'])->count();
    }

    /**
     * Validate required parameters and combine the parameters.
     *
     * @param array $parameters
     * @return void
     * @throws Exception
     */
    public function validateParameters(array $parameters = [])
    {
        if (count($this->parameters) > count($parameters)) {
            throw new Exception('Theses parameters is missing ' . implode(',', array_diff_key($this->parameters,$parameters)));
        }

        if (isset($parameters[3])) {
            $this->parameters[] = 'ignoreColumn';
        }

        $this->parameters = array_combine($this->parameters, $parameters);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.unique', ['attribute' => $this->attribute]);
    }
}
