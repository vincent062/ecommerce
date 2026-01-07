<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/create', name: 'order_create')]
    public function index(RequestStack $requestStack, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        // 1. On s'assure que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Connectez-vous pour valider votre commande');
            return $this->redirectToRoute('app_login');
        }

        // 2. On récupère le panier
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        // Si le panier est vide, on redirige
        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        // 3. On crée la GRANDE commande (Order)
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setIsPaid(false); // Pas encore payé
        
        // Génération d'une référence unique (ex: 2023-10-12-5f3a)
        $date = new \DateTimeImmutable();
        $reference = $date->format('Y-m-d') . '-' . uniqid();
        $order->setReference($reference);

        // 4. On parcourt le panier pour créer les DÉTAILS (OrderDetails)
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            
            if ($product) {
                // On crée une ligne de détail
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order); // On lie à la commande principale
                $orderDetails->setProduct($product->getName());
                $orderDetails->setQuantity($quantity);
                $orderDetails->setPrice($product->getPrice());
                $orderDetails->setTotal($product->getPrice() * $quantity);

                // On ajoute au total global
                $total += $orderDetails->getTotal();

                // On dit à Doctrine de sauvegarder cette ligne
                $em->persist($orderDetails);
            }
        }

        $order->setTotal($total);
        $em->persist($order); // On sauvegarde la commande principale

        // 5. On écrit tout en base de données
        $em->flush();

        // 6. On vide le panier (puisque la commande est enregistrée)
        // Note : Dans un vrai site, on vide le panier APRES le paiement. 
        // Ici, on fait simple pour l'instant.
        $session->remove('cart');

        // 7. On affiche une page de succès (ou récapitulatif)
        return $this->render('order/success.html.twig', [
            'order' => $order
        ]);
    }
}