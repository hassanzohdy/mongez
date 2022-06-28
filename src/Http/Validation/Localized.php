<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Http\Validation;

use Illuminate\Validation\Validator;

class Localized
{
    /**
     * @var array
     */
    protected array $localeCodes = [];

    /**
     * @var array
     */
    protected array $localizedValue;

    /**
     * @var string
     */
    protected string $textAttribute = 'text';

    /**
     * @var string
     */
    protected string $localeCodeAttribute = 'localeCode';

    /**
     * Validator Instance
     */
    private Validator $validator;

    /**
     * @var string
     */
    private string $message = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->localeCodes = config('mongez.localeCodes');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @param  array $parameters
     * @param  Validator $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters, Validator $validator): bool
    {
        $this->validator = $validator;

        if (!empty($parameters[0])) {
            $this->textAttribute = $parameters[0];
        }

        if (!$this->validateValue($value, $attribute)) {
            $this->validator->errors()->add($attribute, $this->message);
            return true;
        }

        $isValid = true;

        collect($value)->each(function ($localized) use (&$isValid) {
            $this->localizedValue = $localized;

            if ($this->validateText() && $this->validateLocaleCode()) {
                return true;
            }

            $isValid = false;

            return false;
        });

        if ($this->message) {
            $this->validator->errors()->add($attribute, $this->message);
        }

        return true;
    }

    /**
     * Validate the given value as array
     * 
     * @param  mixed  $value
     * @param  string  $attribute
     * @return bool
     */
    private function validateValue($value, string $attribute): bool
    {
        if (is_array($value)) {
            return true;
        }

        $this->message = trans('validation.array', ['attribute' => $attribute]);

        return false;
    }

    /**
     * Validate `text` key exists in localized value.
     * Validate `text` key is string in localized value.
     *
     * @return bool
     */
    private function validateText(): bool
    {
        $isPresent = array_key_exists($this->textAttribute, $this->localizedValue);

        if (!$isPresent) {
            $this->message = trans('validation.required', ['attribute' => trans("validation.attributes.{$this->textAttribute}")]);

            return false;
        }

        $text = $this->localizedValue[$this->textAttribute];

        if (!is_string($text)) {
            $this->message = trans('validation.string', ['attribute' => trans("validation.attributes.{$this->textAttribute}")]);

            return false;
        }

        return true;
    }

    /**
     * Validate localeCode key as follows:
     * Validate `localeCode` key exists in localized value.
     * Validate `localeCode` key is string in localized value.
     * Validate `localeCode` key is in `config/mongez.localeCodes` values.
     *
     * @return bool
     */
    private function validateLocaleCode(): bool
    {
        $isPresent = array_key_exists($this->localeCodeAttribute, $this->localizedValue);

        $localeCode = $isPresent ? $this->localizedValue[$this->localeCodeAttribute] : null;

        switch (true) {
            case !$isPresent:
                $this->message = trans('validation.required', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
            case !is_string($localeCode):
                $this->message = trans('validation.string', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
            case !in_array($this->localizedValue[$this->localeCodeAttribute], $this->localeCodes):
                $this->message = trans('validation.in', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
        }

        return true;
    }
}
