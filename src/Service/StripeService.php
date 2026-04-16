<?php

namespace App\Service;

use App\Entity\CartItem;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Service gérant l'intégration Stripe Checkout.
 * Utilisé pour simuler un paiement en mode développement (sandbox).
 */
class StripeService
{
    public function __construct(private string $secretKey)
    {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Crée une session Stripe Checkout à partir des articles du panier.
     *
     * @param CartItem[] $cartItems  Articles du panier de l'utilisateur
     * @param string     $successUrl URL de redirection après paiement réussi
     * @param string     $cancelUrl  URL de redirection si l'utilisateur annule
     *
     * @return Session La session Stripe créée
     */
    public function createCheckoutSession(array $cartItems, string $successUrl, string $cancelUrl): Session
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name' => sprintf('%s (taille %s)', $item->getProduct()->getName(), $item->getSize()),
                    ],
                    'unit_amount'  => (int) round((float) $item->getProduct()->getPrice() * 100),
                ],
                'quantity' => $item->getQuantity(),
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => $successUrl,
            'cancel_url'           => $cancelUrl,
        ]);
    }
}
