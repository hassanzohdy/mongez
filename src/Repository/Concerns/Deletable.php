<?php

namespace HZ\Illuminate\Mongez\Repository\Concerns;

trait Deletable
{
    /**
     * Dependency tables of deleting
     *
     * @param array
     */
    protected $deleteDependenceTables = [];

    /**
     * {@inheritDoc}
     */
    public function delete($model): bool
    {
        $model = $this->getModel($model);

        if (!$model) return false;

        if ($this->trigger("deleting", $model, $model->id) === false) return false;

        // delete uploaded files
        foreach (static::UPLOADS as $file) {
            if (!$model->$file) continue;

            if (is_array($model->$file)) {
                foreach ($model->$file as $singleFile) {
                    $this->unlink($singleFile);
                }
            } else {
                $this->unlink($model->$file);
            }
        }

        $model->delete();

        if ($this->isCacheable()) $this->forgetCache($model->id);

        $this->trigger("delete", $model, $model->id);

        return true;
    }

    /**
     * Check if model has deleting depended tables.
     *
     * @return bool
     */
    public function deleteHasDependence(): bool
    {
        return !empty($this->deleteDependenceTables);
    }

    /**
     * Get model deleting depended tables
     *
     * @return array
     */
    public function getDeleteDependencies(): array
    {
        return $this->deleteDependenceTables;
    }

    /**
     * Check if soft delete used or not
     *
     * @return bool
     */
    public function isUsingSoftDelete(): bool
    {
        return static::USING_SOFT_DELETE;
    }

    /**
     * Check if cache is used or not
     * 
     * @return bool
     */
    public function isCacheable(): bool
    {
        return static::USING_CACHE;
    }
}
