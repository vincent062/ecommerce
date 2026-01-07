<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'app_account')]
    public function index(): Response
    {
        // On vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère l'utilisateur connecté
        // Grâce à la relation qu'on a créée (OneToMany), on pourra accéder à ses commandes via app.user.orders dans la vue
        
        return $this->render('account/index.html.twig');
    }
}