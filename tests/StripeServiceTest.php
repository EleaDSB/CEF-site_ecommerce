<?php

namespace App\Tests;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Service\StripeService;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour le service Stripe.
 * Utilise les clés de test Stripe (mode sandbox).
 */
class StripeServiceTest extends TestCase
{
    private function createCartItems(): array
    {
        $product = new Product();
        $product->setName('Blackbelt');
        $product->setPrice('29.90');
        $product->setImage('1.jpeg');

        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@test.com');
        $user->setPassword('hashed');

        $item = new CartItem();
        $item->setProduct($product);
        $item->setUser($user);
        $item->setSize('M');
        $item->setQuantity(1);

        return [$item];
    }

    /**
     * Test : StripeService peut créer une session Checkout en mode test.
     */
    public function testCreateCheckoutSessionReturnsUrl(): void
    {
        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');

        if (!$secretKey || str_starts_with($secretKey, 'sk_test_your')) {
            $this->markTestSkipped('Clé Stripe de test non configurée dans .env.local.');
        }

        $service    = new StripeService($secretKey);
        $items      = $this->createCartItems();
        $successUrl = 'http://localhost:8000/payment/success';
        $cancelUrl  = 'http://localhost:8000/payment/cancel';

        $session = $service->createCheckoutSession($items, $successUrl, $cancelUrl);

        $this->assertNotEmpty($session->id);
        $this->assertNotEmpty($session->url);
        $this->assertStringStartsWith('https://checkout.stripe.com', $session->url);
        $this->assertSame('open', $session->status);
    }

    /**
     * Test : StripeService génère bien un ID de session unique.
     */
    public function testCheckoutSessionHasUniqueId(): void
    {
        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? getenv('STRIPE_SECRET_KEY');

        if (!$secretKey || str_starts_with($secretKey, 'sk_test_your')) {
            $this->markTestSkipped('Clé Stripe de test non configurée dans .env.local.');
        }

        $service = new StripeService($secretKey);
        $items   = $this->createCartItems();

        $session1 = $service->createCheckoutSession($items, 'http://localhost/success', 'http://localhost/cancel');
        $session2 = $service->createCheckoutSession($items, 'http://localhost/success', 'http://localhost/cancel');

        $this->assertNotSame($session1->id, $session2->id);
    }
}
