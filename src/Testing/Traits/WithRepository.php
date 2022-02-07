<?php

declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Traits\Testing;

use HZ\Illuminate\Mongez\Contracts\Repositories\RepositoryInterface;

trait WithRepository
{
    /**
     * Delete last record
     * 
     * @return void
     */
    protected function deleteLastRecord()
    {
        $this->repo()->getQuery()->latest()->first()->delete();
    }

    /**
     * Delete the given record id
     * 
     * @param  int $id
     * @return void
     */
    protected function deleteRecord(int $id)
    {
        $this->repo()->getQuery()->where('id', $id)->delete();
    }

    /**
     * Set base full accurate data
     * This includes required and optional data
     * 
     * @return RepositoryInterface
     */
    protected function repo(): RepositoryInterface
    {
        return repo($this->getRepositoryName());
    }

    /**
     * A shorthand to get a query builder from current repository
     * 
     * @return Builder
     */
    protected function getQuery()
    {
        return $this->repo()->getQuery();
    }

    /**
     * Get Repository name
     * 
     * @var string
     */
    abstract protected function getRepositoryName(): string;
}
