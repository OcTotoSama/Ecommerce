<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ClientCommandeController extends AbstractController
{
    // Liste des commandes du client
    #[Route('/mes-commandes', name: 'app_mes_commandes')]
    public function liste(CommandeRepository $repo): Response
    {
        $commandes = $repo->findBy(
            ['user' => $this->getUser()],
            ['dateCommande' => 'DESC']
        );

        return $this->render('commande/mes-commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    // Détail + chat commande
    #[Route('/mes-commandes/{id}', name: 'app_mes_commandes_detail')]
    public function detail(
        int $id,
        CommandeRepository $repo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $commande = $repo->find($id);

        if (!$commande || $commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Marquer messages admin comme lus
        foreach ($commande->getMessages() as $message) {
            if ($message->isAdmin() && !$message->isLu()) {
                $message->setLu(true);
            }
        }

        if ($request->isMethod('POST')) {
            $contenu = trim($request->request->get('contenu', ''));
            if ($contenu !== '') {
                $message = new Message();
                $message->setCommande($commande);
                $message->setAuteur($this->getUser());
                $message->setContenu($contenu);
                $message->setIsAdmin(false);
                $em->persist($message);
                $em->flush();
        
                // ✅ Réponse JSON si appel AJAX
                if ($request->isXmlHttpRequest()) {
                    return new \Symfony\Component\HttpFoundation\JsonResponse([
                        'success'   => true,
                        'contenu'   => $message->getContenu(),
                        'dateEnvoi' => $message->getDateEnvoi()->format('d/m H:i'),
                        'isAdmin'   => false,
                    ]);
                }
            }
        }
        
        $em->flush(); // garder pour le marquage "lu"

        return $this->render('commande/detail.html.twig', [
            'commande' => $commande,
        ]);
    }
}