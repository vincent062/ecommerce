<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@createch.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // On crypte le mot de passe "password"
        $password = $this->encoder->hashPassword($admin, 'password');
        $admin->setPassword($password);

        // On remplit les champs obligatoires que tu as créés
        $admin->setFirstname('Admin');
        $admin->setLastname('System');
        
        // Persister et envoyer en base de données
        $manager->persist($admin);
        $manager->flush();
    }
}
