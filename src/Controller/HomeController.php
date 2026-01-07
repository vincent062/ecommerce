<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        // On récupère tous les produits de la base de données
        $products = $productRepository->findBy([], limit:10);

        // On envoie la liste des produits à la vue (le fichier HTML)
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
    // --- NOUVELLE MÉTHODE POUR LA PAGE PRODUIT ---
    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // Grâce à "Product $product", Symfony comprend qu'il doit chercher
        // le produit qui correspond à l'ID dans l'URL. C'est magique !
        
        return $this->render('home/show.html.twig', [
            'product' => $product,
        ]);
    }
}
