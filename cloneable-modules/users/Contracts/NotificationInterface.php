<?php
namespace App\Modules\Users\Contracts;

interface NotificationInterface 
{
    /**
     * Get notification type
     * 
     * @return string
     */
    public function type(): string;

    /**
     * Get notification image
     * 
     * @return string
     */
    public function image(): string;

    /**
     * Get notification extra info
     * 
     * @return array
     */
    public function extra(): array;
}