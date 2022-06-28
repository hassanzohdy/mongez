<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Http\Validation;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class Unique implements Rule
{
    /**
     * @var string
     */
    private string $table;

    /**
     * @var string
     */
    private string $column;

    /**
     * @var
     */
    private $ignoreValue;

    /**
     * @var string
     */
    private string $ignoreColumn;

    /**
     * @var string
     */
    private string $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $table, string $column, $ignoreValue = null, string $ignoreColumn = 'id')
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreValue = $ignoreValue;
        $this->ignoreColumn = $ignoreColumn;
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
        $this->attribute = $attribute;

        return !DB::table($this->table)->where($this->column, $value)->where($this->ignoreColumn, '!=', $this->ignoreValue)->count();
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
