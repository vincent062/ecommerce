<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security; // Pour connecter l'utilisateur
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est déjà connecté, on le renvoie à l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1. On hache (crypte) le mot de passe
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // 2. On sauvegarde en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // 3. ON CONNECTE AUTOMATIQUEMENT L'UTILISATEUR
            return $security->login($user, AppAuthenticator::class, 'main');
            
            // Note : Après le login, Symfony redirige généralement vers la page configurée dans l'Authenticator (souvent l'accueil)
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}