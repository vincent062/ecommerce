<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // Symfony récupère automatiquement le produit grâce à l'ID dans l'URL
        // Si l'ID n'existe pas, il affichera une erreur 404 (Page non trouvée) automatiquement.

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}