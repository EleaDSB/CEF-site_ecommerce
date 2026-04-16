<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CartItemRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    #[Route('/payment/checkout', name: 'app_payment_checkout')]
    public function checkout(
        CartItemRepository $cartItemRepository,
        StripeService $stripeService,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user  = $this->getUser();
        $items = $cartItemRepository->findByUser($user);

        if (empty($items)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $successUrl = $this->generateUrl('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl  = $this->generateUrl('app_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $session = $stripeService->createCheckoutSession($items, $successUrl, $cancelUrl);

        // Crée la commande en base avec le statut "pending"
        $total = $cartItemRepository->calculateTotal($user);
        $order = new Order();
        $order->setUser($user);
        $order->setTotal((string) $total);
        $order->setStatus(Order::STATUS_PENDING);
        $order->setStripeSessionId($session->id);
        $em->persist($order);
        $em->flush();

        // Redirige vers la page de paiement Stripe
        return $this->redirect($session->url, 303);
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function success(
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // Met à jour le statut de la dernière commande
        $lastOrder = $em->getRepository(Order::class)->findOneBy(
            ['user' => $user, 'status' => Order::STATUS_PENDING],
            ['createdAt' => 'DESC']
        );

        if ($lastOrder) {
            $lastOrder->setStatus(Order::STATUS_PAID);
            $em->flush();
        }

        // Vide le panier
        foreach ($cartItemRepository->findByUser($user) as $item) {
            $em->remove($item);
        }
        $em->flush();

        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $this->addFlash('error', 'Paiement annulé. Votre panier a été conservé.');
        return $this->redirectToRoute('app_cart');
    }
}
