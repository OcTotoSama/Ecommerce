<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FavoriController extends AbstractController
{
    /*#[Route('/private-favori/{id}', name: 'app_favori')]
    public function index(Produit $produit, EntityManagerInterface $em, Request $request): Response
    {

    $referer = $request -> headers -> get('referer'); // récupère la page d'ou tu viens

    $u = $this->getUser(); // récupère le user qui est connecté
    if ($u->getProduits()->contains($produit)) {
    $u->removeProduit($produit);
    } else {
    $u->addProduit($produit);
    }
    $em->persist($u);
    $em->flush();
    return $this->redirect($referer ?? $this -> generateUrl('app_accueil')); // renvois sur la page d'ou tu viens
    }*/

    #[Route('/private-favori/{id}', name: 'app_favori', methods: ['POST'])]
    public function index(Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Non authentifié',
            ], 401);
        }

        if ($user->getProduits()->contains($produit)) {
            $user->removeProduit($produit);
            $isFavorite = false;
        } else {
            $user->addProduit($produit);
            $isFavorite = true;
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'isFavorite' => $isFavorite,
        ]);
    }

    #[Route('/private-liste-favoris', name: 'app_liste_favoris')]
    public function listeFavoris(ProduitRepository $produitRepository): Response
    {
        return $this->render('favori/liste-favoris.html.twig');
    }

    /*-----------------------------------FAVORIS---------------------------------------*/
    /*  #[Route('/private-favori/{id}', name: 'app_favori', methods: ['POST'])]
    public function index(Produit $produit, EntityManagerInterface $em, Request $request): Response
    {
    $u = $this->getUser();

    if (!$u) {
    return new JsonResponse([
    'success' => false,
    ], 401);
    }

    // 🔥 version SAFE sans Doctrine contains()
    $isFavorite = false;

    foreach ($u->getProduits() as $p) {
    if ($p->getId() === $produit->getId()) {
    $isFavorite = true;
    break;
    }
    }

    if ($isFavorite) {
    $u->removeProduit($produit);
    $isFavorite = false;
    } else {
    $u->addProduit($produit);
    $isFavorite = true;
    }

    $em->flush();

    return new JsonResponse([
    'success' => true,
    'isFavorite' => $isFavorite,
    ]);
    }*/
    /*-----------------------------------------------------------------------------------*/
}
