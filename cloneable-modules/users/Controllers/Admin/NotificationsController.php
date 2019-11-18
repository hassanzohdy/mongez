<?php
namespace App\Modules\Users\Controllers\Admin;

class NotificationsController extends MeController
{
    /** 
     * Mark the given notification id as seen
     * 
     * @param   int $notificationId
     * @return  mixed  
    */
    public function markAsSeen($notificationId)
    {        
        $this->user = user();

        $this->user->notificationsCenter()->markAsSeen((int) $notificationId);

        return $this->successUser();
    }
}