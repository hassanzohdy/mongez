<?php

namespace HZ\Illuminate\Mongez\Http;

use HZ\Illuminate\Mongez\Traits\WithCommonRules;
use HZ\Illuminate\Mongez\Traits\WithRepositoryAndService;
use HZ\Illuminate\Mongez\Translation\Traits\Translatable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ApiFormRequest extends FormRequest
{
    use ApiResponse, WithRepositoryAndService, Translatable, WithCommonRules;

//    /**
//     * Define request inputs to cast to integer before validation.
//     *
//     * @var array
//     */
//    public array $intInputs = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return void
     */
    public function prepareForValidation(): void
    {
        if (!$this->intInputs) {
            return;
        }

        $this->setIntInputs($this->intInputs);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new ValidationException($validator, $this->badRequest($validator->errors()));
    }

    /**
     * Set int values from request inputs.
     * Help to validate IDs by convert them to int values.
     * If using ApiFormRequest class you have to pass the request to have the int values.
     *
     * @param array $inputs
     * @return void
     */
    public function setIntInputs(array $inputs): void
    {
        $requestKeys = array_unique(array_map(function ($input) {
            return str_contains($input, '.') ? explode('.', $input)[0] : $input;
        }, $inputs));

        $requestInputs = $this->only($requestKeys);

        $requestInputsDotNotation = Arr::dot($requestInputs);

        collect($requestInputsDotNotation)->each(function ($value, $key) use ($inputs, &$requestInputs) {
            $keyArray = str_contains($key, '.') ? explode('.', $key) : [$key];

            $filteredKeyArray = array_filter($keyArray, function ($item) {
                return preg_match_all('!\d+!', $item) === 1;
            });

            $filteredKey = implode('.', array_diff_assoc($keyArray, $filteredKeyArray));

            if (in_array($filteredKey, $inputs) && $value) {
                Arr::set($requestInputs, $key, (int) $value);
            }
        })->toArray();

        $this->merge($requestInputs);
    }
}
