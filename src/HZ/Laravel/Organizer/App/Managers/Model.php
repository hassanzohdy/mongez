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
    protected $createdBy = 'created_by';

    /**
     * Updated By column
     * Set it to false if this column doesn't exist in the table
     *
     * @var string|bool
     */
    protected $updatedBy = 'updated_by';

    /**
     * Deleted By column
     * Set it to false if this column doesn't exist in the table
     *
     * @var string|bool
     */
    protected $deletedBy = 'deleted_by';

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
            if ($model->created_by) {
                $model->created_by = user()->id;
            } 
        });
        
        // before updating, we will check if the updated_by column has value
        // if so, then we will update the column for the current user id
        static::updating(function ($model) {
            if ($model->updated_by) {
                $model->updated_by = user()->id;
            } 
        });

        // before deleting, we will check if the deleted_by column has value
        // if so, then we will update the column for the current user id
        static::deleting(function ($model) {
            if ($model->deleted_by && $model->uses('softDelete')) {
                $model->deleted_by = user()->id;
            } 
        });
    }
}
