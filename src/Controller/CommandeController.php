<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CommandeController extends AbstractController
{
    // Page récapitulatif avec cases à cocher
    #[Route('/commande/recapitulatif', name: 'app_commande_recapitulatif')]
    public function recapitulatif(): Response
    {
        $panier = $this->getUser()->getPanier();

        if (!$panier || $panier->getAjouters()->isEmpty()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('commande/recapitulatif.html.twig', [
            'ajouters' => $panier->getAjouters(),
        ]);
    }

    // Traitement de la commande
    #[Route('/commande/passer', name: 'app_commande_passer', methods: ['POST'])]
    public function passerCommande(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $panier = $user->getPanier();

        // Récupère les IDs cochés envoyés par le formulaire
        $idsSelectionnes = $request->request->all('ajouter_ids');

        if (empty($idsSelectionnes)) {
            $this->addFlash('warning', 'Aucun article sélectionné.');
            return $this->redirectToRoute('app_commande_recapitulatif');
        }

        $commande = new Commande();
        $commande->setUser($user);

        foreach ($panier->getAjouters() as $ajouter) {
            if (in_array($ajouter->getId(), $idsSelectionnes)) {
                // Créer une ligne de commande
                $ligne = new LigneCommande();
                $ligne->setProduit($ajouter->getProduit());
                $ligne->setQuantite($ajouter->getQuantite());
                $ligne->setPrixUnitaire($ajouter->getProduit()->getPrix());
                $commande->addLigneCommande($ligne);
                $em->persist($ligne);

                // Supprimer du panier
                $panier->removeAjouter($ajouter);
                $em->remove($ajouter);
            }
        }

        $em->persist($commande);
        $em->flush();

        $this->addFlash('success', 'Commande passée avec succès !');
        return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
    }

    // Page de confirmation
    #[Route('/commande/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        // Sécurité : l'utilisateur ne peut voir que ses propres commandes
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/admin/commande/supprimer/{id}', name: 'app_admin_commande_supprimer', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($commande);
        $em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/mes-commandes/supprimer/{id}', name: 'app_mes_commandes_supprimer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerMaCommande(Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        // Vérifier que la commande appartient bien à l'utilisateur connecté
        if ($commande->getUser() !== $this->getUser()) {
            return $this->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $em->remove($commande);
        $em->flush();

        return $this->json(['success' => true]);
    }

}
