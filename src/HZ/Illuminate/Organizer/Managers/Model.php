<?php
namespace HZ\Illuminate\Organizer\Managers;

use HZ\Illuminate\Organizer\Traits\ModelTrait;
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
