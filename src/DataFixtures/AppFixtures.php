<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Créer un Admin
        $admin = new User();
        $admin->setEmail('admin@goldenhour.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setFirstname('Vincent'); // OBLIGATOIRE : Défini dans User.php
        $admin->setLastname('Admin');    // OBLIGATOIRE : Défini dans User.php
        
        $password = $this->hasher->hashPassword($admin, 'password');
        $admin->setPassword($password);
        $manager->persist($admin);

        // 2. Créer les Catégories
        $categories = [];
        $names = ['Senteurs d\'Intérieur', 'Textile & Confort', 'Lumière & Déco'];

        foreach ($names as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
            $categories[] = $category;
        }

        // 3. Créer des Produits
        $productsData = [
            // Senteurs
            ['Bougie "Soir d\'Été"', 'Une fragrance chaude mêlant ambre et figue pour vos soirées.', 2490, 0, 'candle1.jpg'],
            ['Diffuseur "Bois de Santal"', 'Bâtonnets parfumés pour une ambiance zen et durable.', 3500, 0, 'diffuser.jpg'],
            ['Spray d\'Ambiance "Linge Propre"', 'Pour rafraîchir vos textiles avec une note légère.', 1990, 0, 'spray.jpg'],
            
            // Textile
            ['Plaid en Maille Ocre', 'Plaid tricoté main, couleur terre cuite, ultra doux.', 8900, 1, 'plaid.jpg'],
            ['Coussin Velours Rose Poudré', 'Housse en velours soyeux pour adoucir votre canapé.', 2900, 1, 'cushion.jpg'],
            ['Tapis Berbère Minimaliste', 'Laine naturelle, motifs géométriques simples.', 12000, 1, 'rug.jpg'],

            // Lumière (J'ai ajouté les images manquantes ici)
            ['Lampe à Poser "Mushroom"', 'Design vintage des années 70, lumière douce.', 14500, 2, 'lamp.jpg'],
            ['Guirlande Guinguette', 'Pour illuminer votre terrasse ou votre salon.', 3990, 2, 'garland.jpg'],
            ['Vase en Céramique Artisanale', 'Façonné à la main, chaque pièce est unique.', 4500, 2, 'vase.jpg'],
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data[0]);
            $product->setDescription($data[1]);
            $product->setPrice($data[2] / 100); 
            $product->setStock(rand(0, 20));
            $product->setCategory($categories[$data[3]]);
            
            // SÉCURITÉ : On s'assure qu'il y a toujours une image
            if (isset($data[4])) {
                $product->setImage($data[4]);
            } else {
                $product->setImage('default.jpg'); // Image de secours au cas où
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}