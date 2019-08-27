<?php
namespace App\Modules\Users\Traits;

use App\Modules\Users\Helpers\Notification;

trait Notify 
{
    /**
     * Create new notification using an array 
     * 
     * @param  array $notificationOptions
     * @return void
     */     
    public function notify(array $notificationOptions)
    {
        $notification = new Notification($notificationOptions['type'], $notificationOptions['image'] ?? '', $notificationOptions['extra'] ?? []);

        $notificationCenter = $this->notificationsCenter();

        $notificationCenter->send($notification);
    }
}