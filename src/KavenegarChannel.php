<?php

namespace Kavenegar\LaravelNotification;

use Illuminate\Notifications\Notification;
use Kavenegar\KavenegarApi;

class KavenegarChannel
{
    protected $api;

    protected const AVAILABLE_METHODS = [
        'Send',
        'VerifyLookup',
    ];

    /**
     * KavenegarChannel constructor.
     * @param KSP $api
     */
    public function __construct(KavenegarApi $api)
    {
        $this->api = $api;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     * @throws Exception
     */
    public function send($notifiable, Notification $notification)
    {
        $method = $notification->getMethod();
        $params = [];
        if (!in_array($method, self::AVAILABLE_METHODS))
        {
            // TODO: Throw exception
        }
        switch($method)
        {
            case 'Send':
            {
                // $params = [$sender, $receptor, $message];
                $params = [
                    config('services.kavenegar.sender'), 
                    $notifiable->routeNotificationFor('sms'), 
                    $notification->toSMS($notifiable),
                ];
                break;
            }
            case 'VerifyLookup':
            {
                // $params = [$receptor, $token, $token2, $token3, $template, $type];
                $tokens = $notifiable->getTokens($notification->getIntent());
                $tokens[0] = isset($tokens[0]) ? $tokens[0] : null;
                $tokens[1] = isset($tokens[1]) ? $tokens[1] : null;
                $tokens[2] = isset($tokens[2]) ? $tokens[2] : null;
                $params = [
                    $notifiable->routeNotificationFor('sms'), 
                    $tokens[0],
                    $tokens[1],
                    $tokens[2],
                    $notification->getTemplate(),
                    $notification->getType(),
                ];
                break;
            }
        }
        $response = $this->api->{$method}(...$params);
        // TODO: Store in database
    }

}
