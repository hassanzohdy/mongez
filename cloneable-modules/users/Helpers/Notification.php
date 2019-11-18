<?php
namespace App\Modules\Users\Helpers;

use App\Modules\Users\Contracts\NotificationInterface;

class Notification implements NotificationInterface
{
    /**
     * Notification type
     * 
     * @var string
     */
    protected $type;

    /**
     * Notification image
     * 
     * @var string
     */
    protected $image;

    /**
     * Notification extra info
     * 
     * @var string
     */
    protected $extra = [];

    /**
     * Constructor
     * 
     * @param  string $type
     * @param  string $image
     * @param  array  $extra
     */
    public function __construct(string $type,string $image = null, array $extra = [])
    {
        $this->type = $type;
        $this->image = $image;
        $this->extra = $extra;
    }

    /**
     * Set extra data
     * 
     * @param  array $info
     * @return $this
     */
    public function setExtra(array $info): NotificationInterface
    {
        $this->extra = $info;
        
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function image(): string
    {
        return $this->image;
    }

    /**
     * {@inheritDoc}
     */
    public function extra(): array
    {
        return $this->extra;
    }
}