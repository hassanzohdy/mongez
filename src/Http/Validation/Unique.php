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
    protected array $parameters = ['table', 'column', 'ignoreValue'];

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param $parameters
     * @return bool
     * @throws Exception
     */
    public function passes(string $attribute, $value, $parameters, Validator $validator): bool
    {
        $this->validateParameters($parameters);

        $this->attribute = $attribute;

        $countCheck = !DB::table($this->parameters['table'])->where($this->parameters['column'], $value)->where($this->parameters['ignoreColumn'] ?? 'id', '!=', (int) $this->parameters['ignoreValue'])->count();

        if (!$countCheck) {
            $validator->errors()->add($attribute, trans('validation.unique', ['attribute' => $this->attribute]));

            return false;
        }

        return true;
    }

    /**
     * Validate required parameters and combine the parameters.
     *
     * @param array $parameters
     * @return void
     * @throws Exception
     */
    protected function validateParameters(array $parameters = [])
    {
        if (count($this->parameters) > count($parameters)) {
            throw new Exception('Theses parameters is missing ' . implode(',', array_diff_key($this->parameters,$parameters)));
        }

        if (isset($parameters[3])) {
            $this->parameters[] = 'ignoreColumn';
        }

        $this->parameters = array_combine($this->parameters, $parameters);
    }
}
