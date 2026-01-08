<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
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
    public function add($id, Request $request, RequestStack $requestStack, ProductRepository $productRepository): Response
    {
        // 1. On récupère le panier et le produit
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);
        
        $product = $productRepository->find($id);

        // Sécurité : si le produit n'existe pas ou plus
        if (!$product) {
            return $this->redirectToRoute('cart_index');
        }

        // 2. On récupère la quantité souhaitée (1 par défaut)
        $qty = (int)$request->request->get('qty', 1);
        if ($qty < 1) { $qty = 1; }

        // 3. Calcul de la quantité future dans le panier
        // (Quantité actuelle + Nouvelle quantité)
        $currentQtyInCart = $cart[$id] ?? 0;
        $totalQtyWanted = $currentQtyInCart + $qty;

        // 4. VÉRIFICATION DU STOCK
        if ($product->getStock() < $totalQtyWanted) {
            // Stock insuffisant : on affiche un message et on n'ajoute PAS
            $remainingStock = $product->getStock() - $currentQtyInCart;
            
            // Petit message sympa pour l'utilisateur
            if ($remainingStock > 0) {
                $this->addFlash('warning', "Désolé, il ne reste que $remainingStock exemplaire(s) disponible(s).");
            } else {
                $this->addFlash('warning', "Désolé, ce produit n'est plus disponible en quantité suffisante.");
            }
            
            return $this->redirectToRoute('cart_index');
        }

        // 5. Si tout est bon, on ajoute au panier
        if (!empty($cart[$id])) {
            $cart[$id] += $qty;
        } else {
            $cart[$id] = $qty;
        }

        $session->set('cart', $cart);

        // Feedback positif optionnel
        // $this->addFlash('success', 'Produit ajouté au panier !');

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