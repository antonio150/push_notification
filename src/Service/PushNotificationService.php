<?php 

namespace App\Service;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    
    public function send(string $title, string $message, array $subscribers): array
    {
        
        if(empty($subscribers)) {
            return ['success' => 0, 'failure' => 0];
        }

        $publicKey = $_ENV['VAPID_PUBLIC_KEY'];
        $privateKey = $_ENV['VAPID_PRIVATE_KEY'];

        if(empty($publicKey) || empty($privateKey)) {
            throw new \Exception('VAPID keys are not set in environment variables.');
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:contact@mail.com',
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        foreach ($subscribers as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->getEndpoint(),
                'keys' => [
                    'p256dh' => $sub->getPublicKey(),
                    'auth' => $sub->getAuthToken(),
                ]
                ]);

                $webPush->queueNotification(
                    $subscription,
                    json_encode([
                        'title' => $title,
                        'message' => $message,
                    ])
                );
        }

        $success = 0;
        $failure = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $success++;
            } else {
                $failure++;
            }
        }

        return ['success' => $success, 'failure' => $failure];
    }
}