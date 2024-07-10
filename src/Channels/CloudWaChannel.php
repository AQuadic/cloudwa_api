<?php

namespace AQuadic\Cloudwa\Channels;

use AQuadic\Cloudwa\Cloudwa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Notifications\Notification;

class CloudWaChannel
{
    /**
     * Send the given notification.
     * @throws ConnectionException|\Exception
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toCloudWa')) {
            throw new \Exception('toCloudWa Method not added yet in notification class');
        }

        $message = $notification->toCloudWa($notifiable);

        (new Cloudwa())
            ->session($message['uuid'] ?? $message['session_uuid'] ?? null)
            ->file($message['image'] ?? $message['file'] ?? null)
            ->phone($message['phones'] ?? $message['phone'] ?? null)
            ->message(
                $message['message'] ?? $message['text'] ?? $message['otp'] ?? $message['code'] ?? null,
            )
            ->throw()
            ->sendMessage();
    }
}
