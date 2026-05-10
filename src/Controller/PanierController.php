<?php

namespace App\Controller;

use App\Entity\Ajouter;
use App\Entity\Panier;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
 
/*------------------------------------------------------------------------------------------------------------------*/

    #[Route('/private-panier/{id}', name: 'app_panier', methods: ['GET', 'POST'])]
    public function index(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');

        $u = $this->getUser();
        if (!$u) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Non connecté'], 401);
            }
            return $this->redirectToRoute('app_login');
        }

        $panier = $u->getPanier();

        if (!$panier) {
            $panier = new Panier();
            $u->setPanier($panier);
        }

        $trouver = false;
        $ajouterTrouver = null;
        $total = $panier->getAjouters()->count();
        $i = 0;

        if ($total) {
            do {
                $ajouter = $panier->getAjouters()->get($i);
                if ($ajouter->getProduit() == $produit) {
                    $trouver = true;
                    $ajouterTrouver = $ajouter;
                }
                $i++;
            } while (!$trouver && $i < $total);
        }

        if ($trouver) {
            $ajouter = $ajouterTrouver;
            $ajouter->setQuantite($ajouter->getQuantite() + 1);
        } else {
            $ajouter = new Ajouter();
            $ajouter->setQuantite(1);
            $ajouter->setProduit($produit);
            $ajouter->setPanier($panier);
        }

        $em->persist($u);
        $em->persist($panier);
        $em->persist($ajouter);
        $em->flush();
        $em->refresh($panier);

        // 🔥 Calcul total panier (optionnel mais utile)
        $totalItems = 0;
        foreach ($panier->getAjouters() as $item) {
            $totalItems += $item->getQuantite();
        }

        //------------
        $prixtotal = 0;
        foreach ($panier->getAjouters() as $item) {
            $prixtotal += $item->getProduit()->getPrix() * $item->getQuantite();
        }
        $prixTVA = $prixtotal * 0.2;
        $prixTotalTVA = $prixtotal + $prixTVA;
        //----------------

        // ✅ CAS AJAX → PAS de reload
        if ($request->isXmlHttpRequest()) {
            /* return new JsonResponse([
            'success' => true,
            'total' => $totalItems
            ]);*/
            return new JsonResponse([
                'success' => true,
                'totalItems' => $totalItems,
                'subtotal' => $prixtotal,
                'tva' => $prixTVA,
                'total' => $prixTotalTVA,
                'quantite' => $ajouter->getQuantite()
            ]);
        }

        // ✅ CAS NORMAL → comportement actuel conservé
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }
/*--------------------------------------------------------------------------------*/



    #[Route('/private-panier-supprime/{id}', name: 'app_panier_supprime', methods: ['GET', 'POST'])]
    public function supprimerarticle(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');

        $u = $this->getUser();
        $panier = $u->getPanier();

        if (!$panier) {
            $panier = new Panier();
            $u->setPanier($panier);
        }

        $trouver = false;
        $ajouterTrouver = null;
        $total = $panier->getAjouters()->count();
        $i = 0;

        if ($total) {
            do {
                $ajouter = $panier->getAjouters()->get($i);
                if ($ajouter->getProduit() == $produit) {
                    $trouver = true;
                    $ajouterTrouver = $ajouter;
                }
                $i++;
            } while (!$trouver && $i < $total);
        }

        $removed = false;

        if ($trouver) {
            if (($ajouter->getQuantite()) > 1) {
                $ajouter = $ajouterTrouver;
                $ajouter->setQuantite(($ajouter->getQuantite()) - 1);
                $em->persist($ajouter);
                $em->flush();
            } else {
                $em->remove($ajouter);
                $em->flush();
                $removed = true;
            }
        }

        // 🔥 recalcul total panier
        $totalItems = 0;
        foreach ($panier->getAjouters() as $item) {
            $totalItems += $item->getQuantite();
        }
        //------------
        $prixtotal = 0;
        foreach ($panier->getAjouters() as $item) {
            $prixtotal += $item->getProduit()->getPrix() * $item->getQuantite();
        }
        $prixTVA = $prixtotal * 0.2;
        $prixTotalTVA = $prixtotal + $prixTVA;


        //----------------

        // ✅ AJAX → pas de reload
        if ($request->isXmlHttpRequest()) {
            /* return new JsonResponse([
            'success' => true,
            'total' => $totalItems,
            'removed' => $removed
            ]);*/
            return new JsonResponse([
                'success' => true,
                /*'total' => $totalItems,*/
                'removed' => $removed,
                'success' => true,
                'totalItems' => $totalItems,
                'subtotal' => $prixtotal,
                'tva' => $prixTVA,
                'total' => $prixTotalTVA,
                'quantite' => $removed ? 0 : $ajouter->getQuantite()
            ]);
        }

        // ✅ fallback → comportement actuel
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }


/*----------------------------------------------------------------------------------------------------------*/
    #[Route('/private-panier-annihiler/{id}', name: 'app_panier_annihiler', methods: ['GET', 'POST'])]
    public function annihiler(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $referer = $request->headers->get('referer');

        $u = $this->getUser();
        if (!$u) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Non connecté'], 401);
            }
            return $this->redirectToRoute('app_login');
        }

        $panier = $u->getPanier();

        if (!$panier) {
            $panier = new Panier();
            $u->setPanier($panier);
        }

        $trouver = false;
        $ajouterTrouver = null;
        $total = $panier->getAjouters()->count();
        $i = 0;

        if ($total) {
            do {
                $ajouter = $panier->getAjouters()->get($i);

                if ($ajouter->getProduit() == $produit) {
                    $trouver = true;
                    $ajouterTrouver = $ajouter;
                }

                $i++;
            } while (!$trouver && $i < $total);
        }

        // 🔥 suppression totale du produit
        if ($trouver) {
            $em->remove($ajouterTrouver);
            $em->flush();
        }

        $totalItems = 0;
        foreach ($panier->getAjouters() as $item) {
            $totalItems += $item->getQuantite();
        }
        // 🔥 recalcul du panier après suppression
        $prixtotal = 0;

        foreach ($panier->getAjouters() as $item) {
            $prixtotal += $item->getProduit()->getPrix() * $item->getQuantite();
        }

        $prixTVA = $prixtotal * 0.2;
        $prixTotalTVA = $prixtotal + $prixTVA;

        // ✅ AJAX RESPONSE
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'subtotal' => $prixtotal,
                'tva' => $prixTVA,
                'total' => $prixTotalTVA,
                'totalItems' => $totalItems,
            ]);
        }

        // ✅ fallback classique
        return $this->redirect($referer ?? $this->generateUrl('app_accueil'));
    }
/*-----------------------------------------------------------------------------------------------------*/

    #[Route('/private-liste-panier', name: 'app_liste_panier')]
    public function listePanier(): Response
    {
        return $this->render('panier/liste-panier.html.twig');
    }

}
