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
            if (static::CREATED_BY) {
                $model->{static::CREATED_BY} = user()->id ?? 0;
            } 
            
            if (static::UPDATED_BY) {
                $model->{static::UPDATED_BY} = $model->byUser();
            } 

            if (static::DELETED_BY) {
                $model->{static::DELETED_BY} = null;
            } 
        });
        
        // before updating, we will check if the updated_by column has value
        // if so, then we will update the column for the current user id
        static::updating(function ($model) {
            if (static::UPDATED_BY) {
                $model->{static::UPDATED_BY} = $model->byUser();
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
}