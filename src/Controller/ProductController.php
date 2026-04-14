<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $priceRange = $request->query->get('price');
        $products   = $productRepository->findByPriceRange($priceRange);

        return $this->render('product/index.html.twig', [
            'products'    => $products,
            'price_range' => $priceRange,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product', requirements: ['id' => '\d+'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'sizes'   => ['XS', 'S', 'M', 'L', 'XL'],
        ]);
    }

    #[Route('/product/{id}/add-to-cart', name: 'app_cart_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addToCart(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        CartItemRepository $cartItemRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $size = $request->request->get('size');
        $validSizes = ['XS', 'S', 'M', 'L', 'XL'];
        if (!in_array($size, $validSizes, true)) {
            $this->addFlash('error', 'Taille invalide.');
            return $this->redirectToRoute('app_product', ['id' => $id]);
        }

        $user = $this->getUser();

        // Vérifie si le produit est déjà dans le panier avec la même taille
        $existingItem = $em->getRepository(CartItem::class)->findOneBy([
            'user'    => $user,
            'product' => $product,
            'size'    => $size,
        ]);

        if ($existingItem) {
            $existingItem->setQuantity($existingItem->getQuantity() + 1);
        } else {
            $cartItem = new CartItem();
            $cartItem->setUser($user);
            $cartItem->setProduct($product);
            $cartItem->setSize($size);
            $cartItem->setQuantity(1);
            $em->persist($cartItem);
        }

        $em->flush();
        $this->addFlash('success', sprintf('"%s" (taille %s) ajouté au panier.', $product->getName(), $size));

        return $this->redirectToRoute('app_cart');
    }
}
