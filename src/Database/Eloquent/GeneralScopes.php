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
    public function scopeForUser(Builder $query, int $id, string $userKey = 'user'): void
    {
        $query->where("{$userKey}.id", $id);
    }

    /**
     * Scope to find by customer id
     */
    public function scopeForCustomer(Builder $query, int $id): void
    {
        $this->scopeForUser($query, $id, 'customer');
    }
}
