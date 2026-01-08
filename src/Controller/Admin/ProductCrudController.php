<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du produit'),
            TextField::new('description', 'Description'),

            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            IntegerField::new('stock', 'Stock'),

            ImageField::new('image', 'Image du Produit')
            ->setBasePath('uploads/')
            ->setUploadDir('public/uploads/') // Champ texte temporaire pour l'image
            ->setUploadedFileNamePattern('[randomhash].[extension]') // Renomme le fichier pour éviter les doublons
            ->setRequired(false), // Permet d'éditer le produit sans devoir ré-uploader l'image
            
            // C'est ce champ magique qui permet de choisir la catégorie :
            AssociationField::new('category', 'Catégorie')
        ];
    }
    
}
