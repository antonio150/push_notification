<?php

namespace App\Controller;

use App\Entity\PushSubscription;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationController extends AbstractController
{
    #[Route('/notification', name: 'app_notification')]
    public function index(): Response
    {
        return $this->render('notification/index.html.twig', [
            'vapid_public_key' => $_ENV['VAPID_PUBLIC_KEY'],
        ]);
    }

    #[Route('/subscribe', name: 'app_subscribe', methods: ['POST'])]
    public function subscribe(Request $request, EntityManagerInterface $em): Response
    {
        $date = json_decode($request->getContent(), true);

        $subscription = new PushSubscription();
        $subscription->setEndpoint($date['endpoint']);
        $subscription->setPublicKey($date['keys']['p256dh']);
        $subscription->setAuthToken($date['keys']['auth']);

        $em->persist($subscription);
        $em->flush();

        return new JsonResponse(['status' => 'Subscription saved']);
    }

    #[Route('/send-notification', name: 'app_send_notification', methods: ['POST', 'GET'])]
    public function sendNotification(EntityManagerInterface $em, PushNotificationService $pushService): JsonResponse
    {
        $subscriptions = $em->getRepository(PushSubscription::class)->findAll();

        $result = $pushService->send('Test Notification', 'This is a test push notification.', $subscriptions);
        
        return new JsonResponse([
            'status' => "ok",
            'success' => $result['success'],
            'failure' => $result['failure'],
        ]);

    }
}
