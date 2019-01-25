<?php
namespace HZ\Laravel\Organizer\Managers;

use HZ\Laravel\Organizer\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use ModelTrait;

    /**
     * Disable guarded fields
     *
     * @var array
     */
    protected $guarded = [];
}
