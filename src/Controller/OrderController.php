<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail; // CORRECTION : Singulier (sans 's')
use App\Repository\AddressRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/create', name: 'order_create')]
    public function index(Request $request, SessionInterface $session, ProductRepository $productRepository, AddressRepository $addressRepository, EntityManagerInterface $em): Response
    {
        // 1. Vérif utilisateur
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 2. Vérif Panier
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            return $this->redirectToRoute('app_home');
        }

        // 3. Calculs Panier (pour l'affichage)
        $cartWithData = [];
        $total = 0;
        foreach ($cart as $id => $quantity) {
            $product = $productRepository->find($id);
            if ($product) {
                $cartWithData[] = [
                    'product' => $product,
                    'quantity' => $quantity
                ];
                $total += ($product->getPrice() * $quantity);
            }
        }

        // -----------------------------------------------------------
        // 4. GESTION DU FORMULAIRE (QUAND ON CLIQUE SUR PAYER)
        // -----------------------------------------------------------
        if ($request->isMethod('POST')) {
            // On récupère l'ID de l'adresse choisie
            $addressId = $request->request->get('delivery_address');
            $address = $addressRepository->find($addressId);

            // Création de la commande
            $order = new Order();
            $order->setUser($user);
            $order->setReference(uniqid()); 
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setIsPaid(false); 

            // ENREGISTREMENT DE L'ADRESSE (COPIE)
            if ($address) {
                // J'ai supprimé la variable $deliveryContent inutile ici
                
                $order->setDeliveryName($address->getName());
                $order->setDeliveryAddress($address->getAddress());
                $order->setDeliveryZipcode($address->getZipcode());
                $order->setDeliveryCity($address->getCity());
                $order->setDeliveryCountry($address->getCountry());
            }

            // ENREGISTREMENT DES PRODUITS (ORDER DETAILS)
            foreach ($cart as $id => $quantity) {
                $product = $productRepository->find($id);
                if ($product) {
                    // CORRECTION : J'utilise OrderDetail (Singulier)
                    $orderDetails = new OrderDetail(); 
                    
                    // ATTENTION : Si setOrder() plante, remplace par setMyOrder($order)
                    $orderDetails->setOrder($order); 
                    
                    $orderDetails->setProduct($product->getName());
                    $orderDetails->setQuantity($quantity);
                    $orderDetails->setPrice($product->getPrice());
                    $orderDetails->setTotal($product->getPrice() * $quantity);

                    $em->persist($orderDetails);
                }
            }

            $em->persist($order);
            $em->flush();

            // Une fois la commande validée, on peut vider le panier ! (Optionnel mais conseillé)
            // $session->remove('cart'); 

            return $this->redirectToRoute('order_recap', ['id' => $order->getId()]);
        }

        return $this->render('order/paiement.html.twig', [
            'cart' => $cartWithData,
            'total' => $total,
            'addresses' => $user->getAddresses()
        ]);
    }

    #[Route('/order/recap/{id}', name: 'order_recap')]
    public function recap(Order $order): Response
    {
        return $this->render('order/recap.html.twig', [
            'order' => $order
        ]);
    }
}