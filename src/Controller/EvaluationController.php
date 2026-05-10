<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Repository\EvaluationRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class EvaluationController extends AbstractController
{
    #[Route('/evaluer/{id}', name: 'app_evaluer', methods: ['POST'])]
    public function evaluer(
        int $id,
        Request $request,
        ProduitRepository $produitRepo,
        EvaluationRepository $evalRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user    = $this->getUser();
        $produit = $produitRepo->find($id);

        if (!$produit) {
            return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
        }

        // Vérifier que l'utilisateur a bien acheté ce produit
        $aAchete = false;
        foreach ($user->getCommandes() as $commande) {
            foreach ($commande->getLigneCommandes() as $ligne) {
                if ($ligne->getProduit() === $produit) {
                    $aAchete = true;
                    break 2;
                }
            }
        }

        if (!$aAchete) {
            return new JsonResponse(['success' => false, 'message' => 'Vous n\'avez pas acheté ce produit'], 403);
        }

        $note = (int) $request->request->get('note');
        if ($note < 1 || $note > 5) {
            return new JsonResponse(['success' => false, 'message' => 'Note invalide'], 400);
        }

        // Chercher une évaluation existante (mise à jour) ou en créer une
        $evaluation = $evalRepo->findByUserAndProduit($user, $produit);
        if (!$evaluation) {
            $evaluation = new Evaluation();
            $evaluation->setUser($user);
            $evaluation->setProduit($produit);
            $em->persist($evaluation);
        }

        $evaluation->setNote($note);
        $em->flush();

        // Recalculer la moyenne
        $em->refresh($produit);
        $moyenne = $produit->getMoyenneNotes();

        return new JsonResponse([
            'success' => true,
            'note'    => $note,
            'moyenne' => $moyenne,
        ]);
    }
}