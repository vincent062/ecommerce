<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account/address')]
class AccountAddressController extends AbstractController
{
    #[Route('/', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address/index.html.twig');
    }

    #[Route('/add', name: 'account_address_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $address = new Address();
        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser()); // On lie l'adresse à l'utilisateur
            $em->persist($address);
            $em->flush();

            $this->addFlash('success', 'Votre adresse a bien été ajoutée.');
            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'account_address_edit')]
    public function edit(Request $request, Address $address, EntityManagerInterface $em): Response
    {
        // Sécurité : Vérifier que l'adresse appartient bien à l'utilisateur
        if ($address->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Votre adresse a été modifiée.');
            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'account_address_delete')]
    public function delete(Address $address, EntityManagerInterface $em): Response
    {
        if ($address->getUser() === $this->getUser()) {
            $em->remove($address);
            $em->flush();
            $this->addFlash('success', 'Votre adresse a été supprimée.');
        }

        return $this->redirectToRoute('account_address');
    }
}