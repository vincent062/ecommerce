<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(OrderRepository $orderRepository): Response
    {
        // On récupère les commandes de l'utilisateur, triées par date (la plus récente en haut)
        $orders = $orderRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('account/index.html.twig', [
            'orders' => $orders,
        ]);
    }
}