<?php
namespace HZ\Laravel\Organizer\App\Managers;

use HZ\Laravel\Organizer\App\Traits\ModelTrait;
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
