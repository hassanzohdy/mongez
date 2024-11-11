<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait GeneralScopes
{
    /**
     * Scope to find by another record
     */
    public function scopeFindBy(Builder $query, string $column, mixed $value, string $sign = '='): void
    {
        $query->where($column, $sign, $value);
    }

    /**
     * Scope to find by user id
     */
    public function scopeFor(Builder $query, int $id, string $key = 'user'): void
    {
        $query->where("{$key}.id", $id);
    }
}
