<?php

namespace App\Controller;

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
        $products = $productRepository->findAll();

        // On envoie la liste des produits à la vue (le fichier HTML)
        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
}