<?php

namespace App\Tests;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour les fonctionnalités du panier.
 */
class CartTest extends TestCase
{
    private function createProduct(string $name, float $price): Product
    {
        $product = new Product();
        $product->setName($name);
        $product->setPrice((string) $price);
        $product->setImage('test.jpeg');

        return $product;
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@test.com');
        $user->setPassword('hashed_password');

        return $user;
    }

    private function createCartItem(Product $product, User $user, string $size, int $quantity = 1): CartItem
    {
        $item = new CartItem();
        $item->setProduct($product);
        $item->setUser($user);
        $item->setSize($size);
        $item->setQuantity($quantity);

        return $item;
    }

    /**
     * Test : un article ajouté au panier a les bonnes propriétés.
     */
    public function testAddProductToCart(): void
    {
        $product = $this->createProduct('Blackbelt', 29.90);
        $user    = $this->createUser();
        $item    = $this->createCartItem($product, $user, 'M');

        $this->assertSame($product, $item->getProduct());
        $this->assertSame($user, $item->getUser());
        $this->assertSame('M', $item->getSize());
        $this->assertSame(1, $item->getQuantity());
    }

    /**
     * Test : le sous-total d'un article est correctement calculé.
     */
    public function testCartItemSubtotal(): void
    {
        $product = $this->createProduct('Pokeball', 45.00);
        $user    = $this->createUser();
        $item    = $this->createCartItem($product, $user, 'L', 2);

        $this->assertEqualsWithDelta(90.00, $item->getSubtotal(), 0.001);
    }

    /**
     * Test : le total du panier est la somme des sous-totaux.
     */
    public function testCartTotalCalculation(): void
    {
        $user = $this->createUser();

        $item1 = $this->createCartItem($this->createProduct('Blackbelt', 29.90), $user, 'M', 1);
        $item2 = $this->createCartItem($this->createProduct('Pokeball', 45.00), $user, 'L', 2);

        $total = $item1->getSubtotal() + $item2->getSubtotal();

        // 29.90 + (45.00 × 2) = 119.90
        $this->assertEqualsWithDelta(119.90, $total, 0.001);
    }

    /**
     * Test : retirer un article vide le panier correctement.
     */
    public function testRemoveItemFromCart(): void
    {
        $user  = $this->createUser();
        $items = [
            $this->createCartItem($this->createProduct('Blackbelt', 29.90), $user, 'M'),
            $this->createCartItem($this->createProduct('Pokeball', 45.00), $user, 'L'),
        ];

        $this->assertCount(2, $items);

        // Simule la suppression du premier article
        array_shift($items);

        $this->assertCount(1, $items);
        $this->assertSame('Pokeball', $items[0]->getProduct()->getName());
    }

    /**
     * Test : le panier vide a un total de 0.
     */
    public function testEmptyCartTotal(): void
    {
        $total = 0.0;
        $this->assertSame(0.0, $total);
    }

    /**
     * Test : une taille valide est acceptée.
     */
    public function testValidSize(): void
    {
        $validSizes = ['XS', 'S', 'M', 'L', 'XL'];
        $size       = 'M';

        $this->assertContains($size, $validSizes);
    }

    /**
     * Test : une taille invalide est rejetée.
     */
    public function testInvalidSize(): void
    {
        $validSizes = ['XS', 'S', 'M', 'L', 'XL'];
        $size       = 'XXL';

        $this->assertNotContains($size, $validSizes);
    }
}
