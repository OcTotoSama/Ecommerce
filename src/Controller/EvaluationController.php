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
        try {
            // ✅ Vérification auth à l'intérieur du try pour retourner du JSON
            if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
                return new JsonResponse(['success' => false, 'message' => 'Non connecté'], 401);
            }

            $user    = $this->getUser();
            $produit = $produitRepo->find($id);

            if (!$produit) {
                return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
            }

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
                return new JsonResponse(['success' => false, 'message' => 'Produit non acheté'], 403);
            }

            $note = (int) $request->request->get('note');
            if ($note < 1 || $note > 5) {
                return new JsonResponse(['success' => false, 'message' => 'Note invalide'], 400);
            }

            $evaluation = $evalRepo->findByUserAndProduit($user, $produit);
            if (!$evaluation) {
                $evaluation = new Evaluation();
                $evaluation->setUser($user);
                $evaluation->setProduit($produit);
                $em->persist($evaluation);
            }

            $evaluation->setNote($note);
            $em->flush();

            $em->refresh($produit);
            $moyenne = $produit->getMoyenneNotes();

            return new JsonResponse([
                'success' => true,
                'note'    => $note,
                'moyenne' => $moyenne,
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'file'    => basename($e->getFile()),
                'line'    => $e->getLine(),
            ], 500);
        }
    }
}