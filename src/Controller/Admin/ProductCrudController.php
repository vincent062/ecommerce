<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
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
            // TextField::new('slug', 'Slug')->hideOnIndex(),
            TextField::new('description', 'Description'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            IntegerField::new('stock', 'Stock'),
            TextField::new('image', 'Nom du fichier Image'), // Champ texte temporaire pour l'image
            
            // C'est ce champ magique qui permet de choisir la catégorie :
            AssociationField::new('category', 'Catégorie')
        ];
    }
    
}
