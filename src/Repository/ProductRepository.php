<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne les produits mis en avant sur la page d'accueil.
     */
    public function findFeatured(): array
    {
        return $this->findBy(['featured' => true], null, 3);
    }

    /**
     * Filtre les produits selon une fourchette de prix.
     */
    public function findByPriceRange(?string $range): array
    {
        $qb = $this->createQueryBuilder('p');

        if ($range === '10-29') {
            $qb->where('p.price >= 10 AND p.price < 29');
        } elseif ($range === '29-35') {
            $qb->where('p.price >= 29 AND p.price < 35');
        } elseif ($range === '35-50') {
            $qb->where('p.price >= 35 AND p.price <= 50');
        }

        return $qb->orderBy('p.name', 'ASC')->getQuery()->getResult();
    }
}
