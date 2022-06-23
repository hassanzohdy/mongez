<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Http\Validation;

use Illuminate\Contracts\Validation\Rule;
use HZ\Illuminate\Mongez\Translation\Traits\Translatable;

class Localized implements Rule
{
    use Translatable;

    /**
     * @var array
     */
    private array $localeCodes = [];

    /**
     * @var bool
     */
    private bool $required;

    /**
     * @var array
     */
    private array $localized;

    /**
     * @var string
     */
    private string $textAttribute = 'text';

    /**
     * @var string
     */
    private string $localeCodeAttribute = 'localeCode';

    /**
     * @var bool
     */
    private bool $textPresentable;

    /**
     * @var string
     */
    private string $message;

    /**
     * Constructor.
     *
     * @param bool $required
     */
    public function __construct(bool $required = true)
    {
        $this->required = $required;

        $this->localeCodes = config('app.locale_codes');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->validateValue($value)) {
            return false;
        }

        $validate = collect($value)->map(function ($localized) {
            $this->localized = $localized;

            if ($this->validateText() && $this->validateLocaleCode()) {
                return true;
            }

            return false;
        })->first(function ($value) {
            return $value === false;
        });

        return is_null($validate);
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateValue($value): bool
    {
        if (is_array($value)) {
            return true;
        }

        $this->message = trans('validation.array');

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
        $isPresent = array_key_exists($this->textAttribute, $this->localized);

        $this->textPresentable = $isPresent;

        $text = $isPresent ? $this->localized[$this->textAttribute] : null;

        switch (true) {
            case !$this->required && (!$isPresent || is_null($text)):

                return true;
            case !$isPresent:
                $this->message = trans('validation.required', ['attribute' => trans("validation.attributes.{$this->textAttribute}")]);

                return false;
            case !is_string($text):
                $this->message = trans('validation.string', ['attribute' => trans("validation.attributes.{$this->textAttribute}")]);

                return false;
        }

        return true;
    }

    /**
     * Validate `localeCode` key exists in localized value.
     * Validate `localeCode` key is string in localized value.
     * Validate `localeCode` key is in `config/app.locale_codes` values.
     *
     * @return bool
     */
    private function validateLocaleCode(): bool
    {
        $isPresent = array_key_exists($this->localeCodeAttribute, $this->localized);

        $localeCode = $isPresent ? $this->localized[$this->localeCodeAttribute] : null;

        switch (true) {
            case !$this->required && !$isPresent && !$this->textPresentable:

                return true;
            case !$isPresent:
                $this->message = trans('validation.required', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
            case !is_string($localeCode):
                $this->message = trans('validation.string', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
            case !in_array($this->localized[$this->localeCodeAttribute], $this->localeCodes):
                $this->message = trans('validation.in', ['attribute' => trans("validation.attributes.{$this->localeCodeAttribute}")]);

                return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
