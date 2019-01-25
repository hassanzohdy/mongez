<?php
namespace HZ\Laravel\Organizer\App\Database\MYSQL\Managers;

use HZ\Laravel\Organizer\App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use ModelTrait;
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
     * Disable guarded fields
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
     * Get model info
     * 
     * @return mixed
     */
    public function info()
    {
        return $this->getAttributes();
    }
}