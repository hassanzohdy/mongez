<?php
namespace HZ\Laravel\Organizer\App\Managers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * Created By column
     * Set it to false if this column doesn't exist in the table
     *
     * @var string|bool
     */
    const CREATED_BY = 'created_by';

    /**
     * Updated By column
     * Set it to false if this column doesn't exist in the table
     *
     * @var string|bool
     */
    const UPDATED_BY = 'updated_by';

    /**
     * Deleted By column
     * Set it to false if this column doesn't exist in the table
     *
     * @var string|bool
     */
    const DELETED_BY = 'deleted_by';

    /**
     * Determine if the current model uses the given trait
     *
     * @param  string $trait
     * @return bool
     */
    public function uses(string $trait): bool
    {
        if ($trait == 'softDelete') {
            $trait = SoftDeletes::class;
        }

        return in_array($trait, class_uses($this));
    }

    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        // before creating, we will check if the created_by column has value
        // if so, then we will update the column for the current user id
        static::creating(function ($model) {
            if (static::CREATED_BY) {
                $model->{static::CREATED_BY} = user()->id ?? 0;
            } 
            
            if (static::UPDATED_BY) {
                $model->{static::UPDATED_BY} = user()->id ?? 0;
            } 

            if (static::DELETED_BY) {
                $model->{static::DELETED_BY} = 0;
            } 
        });
        
        // before updating, we will check if the updated_by column has value
        // if so, then we will update the column for the current user id
        static::updating(function ($model) {
            if (static::UPDATED_BY) {
                $model->{static::UPDATED_BY} = user()->id ?? 0;
            } 
        });

        // before deleting, we will check if the deleted_by column has value
        // if so, then we will update the column for the current user id
        static::deleting(function ($model) {
            if (static::DELETED_BY && $model->uses('softDelete')) {
                $model->{static::DELETED_BY} = user()->id ?? 0;
            } 
        });
    }
}