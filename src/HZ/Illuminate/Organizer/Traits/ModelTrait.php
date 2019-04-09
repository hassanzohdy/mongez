<?php
namespace HZ\Illuminate\Organizer\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

trait ModelTrait
{
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
     * {@inheritDoc}
     */
    public static function boot()
    {
        // fixing laravel 5.7 update that we MUST call the parent boot method first 
        parent::boot(); 
        // before creating, we will check if the created_by column has value
        // if so, then we will update the column for the current user id
        static::creating(function ($model) {
            if ($model->createdBy) {
                $model->{$model->createdBy} = user()->id ?? 0;
            } 
            
            if ($model->updatedBy) {
                $model->{$model->updatedBy} = $model->byUser();
            } 

            if ($model->deletedBy) {
                $model->{$model->deletedBy} = null;
            } 
        });
        
        // before updating, we will check if the updated_by column has value
        // if so, then we will update the column for the current user id
        static::updating(function ($model) {
            if ($model->updatedBy) {
                $model->{$model->updatedBy} = $model->byUser();
            } 
        });

        // before deleting, we will check if the deleted_by column has value
        // if so, then we will update the column for the current user id
        static::deleting(function ($model) {
            if ($model->deletedBy && $model->uses(SoftDeletes::class)) {
                $model->{$model->deletedBy} = $model->byUser();
            } 
        });
    }
}