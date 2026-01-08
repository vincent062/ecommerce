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
    // 1. LA PAGE DE RÉCAPITULATIF ET PAIEMENT
    #[Route('/order/checkout', name: 'order_recap')]
    public function recap(RequestStack $requestStack, ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        // On recalcule le total pour l'afficher au client
        $total = 0;
        $items = [];
        
        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $total += $product->getPrice() * $quantity;
                $items[] = ['product' => $product, 'quantity' => $quantity];
            }
        }

        return $this->render('order/recap.html.twig', [
            'items' => $items,
            'total' => $total
        ]);
    }

    // 2. LE TRAITEMENT DE LA COMMANDE (APRÈS PAIEMENT)
    #[Route('/order/validate', name: 'order_validate')]
    public function validate(RequestStack $requestStack, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $session = $requestStack->getSession();
        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        // CRÉATION DE LA COMMANDE
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setIsPaid(true); // ✅ On considère que c'est payé !
        
        $date = new \DateTimeImmutable();
        $reference = $date->format('Y-m-d') . '-' . uniqid();
        $order->setReference($reference);

        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            
            if ($product) {
                // Gestion du stock (Optionnel mais recommandé)
                // $newStock = $product->getStock() - $quantity;
                // $product->setStock($newStock);

                $orderDetails = new OrderDetails();
                $orderDetails->setOrderDetails($order);
                $orderDetails->setProduct($product->getName());
                $orderDetails->setQuantity($quantity);
                $orderDetails->setPrice($product->getPrice());
                $orderDetails->setTotal($product->getPrice() * $quantity);

                $total += $orderDetails->getTotal();
                $em->persist($orderDetails);
            }
        }

        $order->setTotal($total);
        $em->persist($order);
        $em->flush();

        // ON VIDE LE PANIER
        $session->remove('cart');

        return $this->render('order/success.html.twig', [
            'order' => $order
        ]);
    }
    #[Route('/order/{id}', name: 'order_show')]
    public function show(Order $order): Response
    {
        // Sécurité : On vérifie que la commande appartient bien à l'utilisateur connecté
        if ($order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_account');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}