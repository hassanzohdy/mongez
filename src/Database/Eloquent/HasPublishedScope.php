<?php

namespace HZ\Illuminate\Mongez\Database\Eloquent;

use Illuminate\Database\Eloquent\Builder;

trait HasPublishedScope
{
    /**
     * Scope a query to only include published records.
     */
    public function scopePublished(Builder $query, string $status = 'published'): void
    {
        $query->where($status, true);
    }

    /**
     * Scope a query to exclude published records.
     */
    public function scopeNotPublished(Builder $query, string $status = 'published'): void
    {
        $query->where($status, false)->orWhereNull($status);
    }

    /**
     * Scope a query to find a published record.
     */
    public function findPublished(Builder $query, int $id, string $status = 'published'): void
    {
        $query->where($status, true)->where('id', $id);
    }
}
