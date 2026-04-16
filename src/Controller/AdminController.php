<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $em): Response
    {
        $products = $productRepository->findAll();

        // Formulaire d'ajout
        $newProduct = new Product();
        $addForm    = $this->createForm(ProductFormType::class, $newProduct, ['require_image' => true]);
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $imageFile = $addForm->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/images/products', $filename);
                $newProduct->setImage($filename);
            }
            $em->persist($newProduct);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        // Formulaires de modification (un par produit)
        $editForms = [];
        foreach ($products as $product) {
            $editForms[$product->getId()] = $this->createForm(ProductFormType::class, $product, [
                'action' => $this->generateUrl('app_admin_edit', ['id' => $product->getId()]),
            ])->createView();
        }

        return $this->render('admin/index.html.twig', [
            'products'   => $products,
            'add_form'   => $addForm,
            'edit_forms' => $editForms,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_edit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $filename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/images/products', $filename);
                $product->setImage($filename);
            }
            $em->flush();
            $this->addFlash('success', sprintf('"%s" modifié avec succès.', $product->getName()));
        }

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/delete/{id}', name: 'app_admin_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(int $id, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $product = $productRepository->find($id);
        if ($product) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', sprintf('"%s" supprimé.', $product->getName()));
        }

        return $this->redirectToRoute('app_admin');
    }
}
