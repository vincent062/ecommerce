<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_index')]
    public function index(RequestStack $requestStack, ProductRepository $productRepository): Response
    {
        // 1. On récupère le panier de la session (vide par défaut)
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        // 2. On "transforme" la liste d'IDs en liste de vrais produits
        $cartWithData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        // 3. On envoie tout ça à la vue
        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(int $id, RequestStack $requestStack): Response
    {
        // 1. On récupère la session
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        // 2. On ajoute le produit (ou on augmente la quantité)
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        // 3. On sauvegarde le panier dans la session
        $session->set('cart', $cart);

        // 4. On redirige vers le panier
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(int $id, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
}