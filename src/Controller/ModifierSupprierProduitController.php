<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ModifierProduitType;
use App\Form\SupprimerProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ModifierSupprierProduitController extends AbstractController
{
    #[Route('/admin-liste-produits', name: 'app_liste_produits', methods: ['GET', 'POST'])]
    public function liste(Request $request, ProduitRepository $Repository, EntityManagerInterface $em): Response
    {
        $repositorys = $Repository->findAll();
        $form = $this->createForm(SupprimerProduitType::class, null, [
            'returns' => $repositorys,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selected = $form->get('returns')->getData();
            foreach ($selected as $element) {
                $em->remove($element);
            }
            $em->flush();
            $this->addFlash('notice', 'Produits supprimées avec succès');
            return $this->redirectToRoute('app_liste_produits');
        }
        return $this->render('admin/liste-produits.html.twig', [
            'returns' => $repositorys,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin-modifier-produits/{id}', name: 'app_modifier_produits')]
    public function modifier(Request $request, Produit $var, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ModifierProduitType::class, $var);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($var);
                $em->flush();
                $this->addFlash('notice', 'Produit modifiée');
                return $this->redirectToRoute('app_liste_produits');
            }
        }
        return $this->render('admin/modifier-produits.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin-supprimer-produit/{id}', name: 'app_supprimer_produit')]
    public function supprimer(Request $request, Produit $var, EntityManagerInterface $em): Response
    {
        if ($var != null) {
            $em->remove($var);
            $em->flush();
            $this->addFlash('notice', 'Produit supprimée');
        }
        return $this->redirectToRoute('app_liste_produits');
    }

}
