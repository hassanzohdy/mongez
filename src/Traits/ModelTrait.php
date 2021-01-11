<?php

namespace HZ\Illuminate\Mongez\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

trait ModelTrait
{
    /**
     * Set table prefix
     * If set to null, then config:mongez.database.prefix will be used instead
     * 
     * @var string|null
     */
    public $prefix = null;

    /**
     * If set to true, it will disable updated by during timeline
     *
     * @var boolean
     */
    public static $disableUpdateTime = false;

    /**
     * Determine if the current model uses the given trait
     *
     * @param  string $trait
     * @return bool
     */
    public function uses(string $trait): bool
    {
        return in_array($trait, class_uses($this));
    }

    /**
     * Get table name
     * 
     * @return string
     */
    public static function getTableName()
    {
        return (new static)->getTable();
    }
    
    /**
     * An alias method to `getAttributes` method
     * 
     * @return array
     */
    public function info(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        $table = parent::getTable();
        $prefix = '';
        if ($this->prefix !== null) {
            $prefix = $this->prefix;
        } elseif (config('mongez.database.prefix')) {
            $prefix = config('mongez.database.prefix');
        }

        $table = $prefix . $table;

        return $table;
    }

    /**
     * Get model id, if no id yet then return next id
     * 
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?? static::getNextId();
    }

    /**
     * Pluck the given keys from the model info
     * 
     * @param  array $columns
     * @return array
     */
    public function pluck(...$columns): array
    {
        if (func_num_args() == 1 && is_array($columns[0])) {
            $columns = $columns[0];
        }

        return $this->only($columns);
    }
    
    /**
     * Get all attributes except the given columns
     * 
     * @param  array $columns
     * @return array
     */
    public function except(...$columns)
    {
        if (func_num_args() == 1 && is_array($columns[0])) {
            $columns = $columns[0];
        }

        return Arr::except($this->getAttributes(), $columns);
    }

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        // fixing laravel 5.7 update that we MUST call the parent boot method first 
        parent::boot();
        // before creating, we will check if the created_by column has value
        // if so, then we will update the column for the current user id
        static::creating(function ($model) {
            if (static::CREATED_BY && !$model->{static::CREATED_BY}) {
                $model->{static::CREATED_BY} = $model->byUser();
            }

            if (static::UPDATED_BY && !$model->{static::UPDATED_BY}) {
                $model->{static::UPDATED_BY} = $model->byUser();
            }

            if (static::DELETED_BY && !$model->{static::DELETED_BY}) {
                $model->{static::DELETED_BY} = null;
            }
        });

        // before updating, we will check if the updated_by column has value
        // if so, then we will update the column for the current user id
        static::updating(function ($model) {
            if (static::UPDATED_BY) {
                $model->{static::UPDATED_BY} = $model->byUser();
            }

            $updatesLogModel = config('mongez.database.updatesLogModel');

            // if updates log model is set, then the data of current model 
            // will be logged before update happens.
            if ($updatesLogModel) {
                $updatesLogModel::create([
                    'table' => $model->getTableName(),
                    'id' => $model->id,
                    'data' => json_encode($model->getAttributes(), JSON_UNESCAPED_SLASHES),
                ]);
            }
        });

        // before deleting, we will check if the deleted_by column has value
        // if so, then we will update the column for the current user id
        static::deleting(function ($model) {
            if (static::DELETED_BY && $model->uses(SoftDeletes::class)) {
                $model->{static::DELETED_BY} = $model->byUser();
            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($value)
    {
        if (static::$disableUpdateTime) return $this;

        return parent::setUpdatedAt($value);
    }
}
