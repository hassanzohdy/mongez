<?php
namespace App\Modules\Users\Helpers;

use App\Modules\Users\Models\User;
use App\Modules\Users\Contracts\NotificationInterface;
use App\Modules\Users\Models\Notification as NotificationModel;

class notificationsCenter 
{
    /**
     * User model
     * 
     * @var \App\Model\User\User
     */
    private $user;

    /**
     * Notifications list
     * 
     * @var array
     */
    protected $notifications = [];

    /**
     * Constructor
     * 
     * @param  \App\Model\User\User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->notifications = $this->user->notifications ?? [];
    }


    /**
     * Add new notification to user
     * 
     * @param  \App\Modules\Users\Contracts\NotificationInterface $notification
     * @return void
     */
    public function send(NotificationInterface $notification)
    {
        $notificationInfo = [
            'seen' => false,
            'type' => $notification->type(),
            'image' => $notification->image(),
            'extra' => $notification->extra(),
        ];

        $notificationModel = new NotificationModel;

        foreach ($notificationInfo as $key => $value) {
            $notificationModel->$key = $value;
        }

        $notificationModel->by = $this->user->sharedInfo();

        $notificationModel->save();

        $notificationInfo = $notificationModel->info();

        array_unshift($this->notifications, $notificationInfo);

        $notificationInfo['createdAt'] = [
            'timestamp' => now(),
        ];

        $this->createNotificationFile($notificationInfo);

        $this->save();
    }

    /**
     * Mark the given notification id as seen
     * 
     * @param   int $notificationId
     * @return  void
     */
    public function markAsSeen(int $notificationId)
    {        
        foreach ($this->notifications as & $notification) {
            if ($notification['id'] == $notificationId) {
                $notification['seen'] = true;
                break;
            }
        }

        $this->save();
    }

    /**
     * Save user notifications
     * 
     * @return  void
     */
    protected function save()
    {        
        $this->user->notifications = $this->notifications;
        $this->user->save();
    }

    /**
     * Create notification file for the given notification info
     * 
     * @param  array $notificationInfo
     * @return void
     */
    protected function createNotificationFile(array $notificationInfo) 
    {
        $notificationFilePath = storage_path("sockets/users/{$this->user->id}/notifications");

        if (! is_dir($notificationFilePath)) {
            mkdir($notificationFilePath, 07777, true);
        }

        file_put_contents($notificationFilePath . '/' . time() . '.json', json_encode(($notificationInfo)));
    }
}