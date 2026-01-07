<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option 1 : Rediriger vers la liste des commandes par défaut
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OrderCrudController::class)->generateUrl());

        // Option 2 : Afficher le dashboard par défaut (page vide pour l'instant)
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Golden Hour Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('E-commerce');
        // Vérifie bien que ces Entités (Product, Order) existent dans ton projet
        yield MenuItem::linkToCrud('Produits', 'fas fa-box-open', Product::class);
        yield MenuItem::linkToCrud('Commandes', 'fas fa-shopping-cart', Order::class);
        
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Clients', 'fas fa-user', User::class);

        yield MenuItem::section('Navigation');
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-undo', 'app_home');
    }
}