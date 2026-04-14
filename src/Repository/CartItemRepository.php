<?php

namespace App\Repository;

use App\Entity\CartItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    /**
     * Retourne tous les articles du panier pour un utilisateur donné.
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /**
     * Calcule le total du panier pour un utilisateur.
     */
    public function calculateTotal(User $user): float
    {
        $items = $this->findByUser($user);
        $total = 0.0;
        foreach ($items as $item) {
            $total += $item->getSubtotal();
        }
        return $total;
    }
}
