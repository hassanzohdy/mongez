<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Helpers\Validation;

use Illuminate\Contracts\Validation\Rule;

class UniqueEmail implements Rule
{
    /**
     * Repository object
     * 
     * @var \Illuminate\Contracts\Repositories\RepositoryInterface
     */
    protected $repository;
    /**
     * Attribute name
     * 
     * @var string
     */
    protected $attribute;

    /**
     * Column name
     * 
     * @var string
     */
    protected $column;

    /**
     * Except column
     * 
     * @var string
     */
    protected $exceptColumn;

    /**
     * Except column value
     * 
     * @var string
     */
    protected $exceptColumnValue;

    /**
     * Constructor
     * 
     * @param  \Illuminate\Contracts\Repositories\Repository $repository
     * @param  string $column
     * @param mixed $exceptColumnValue
     * @param string $exceptColumn
     */
    public function __construct($repository, $column, $exceptColumnValue = null, $exceptColumn = null)
    {
        $this->repository = $repository;
        $this->column = $column;
        $this->exceptColumnValue = $exceptColumnValue;
        $this->exceptColumn = $exceptColumn;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        
        $query = $this->repository->getQuery();

        if ($this->exceptColumn) {
            $query->where($this->exceptColumn, '!=', $this->exceptColumnValue);
        }

        return $query->where($this->column, strtolower($value))->exists() === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('mongez::validation.uniqueEmail', ['attribute' => $this->attribute]);
        return 'The :attribute must be uppercase.';
    }
}
