<?php
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\Notification as BaseNotification;

function notifyUser($user, array $data)
{
    $user->notify(new class($data) extends BaseNotification {
        public $id;

        public function __construct(public $data) {}

        public function via($notifiable)
        {
            return ['database'];
        }

        public function toDatabase($notifiable)
        {
            return $this->data;
        }
    });
}
