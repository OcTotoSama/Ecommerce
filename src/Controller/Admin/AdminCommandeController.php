<?php
namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Entity\Message;
use App\Form\EtatCommandeType;
use App\Repository\CommandeRepository;/*
use App\Repository\MessageRepository;*/
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_ADMIN')]
class AdminCommandeController extends AbstractController
{
    // Liste commandes — non lues en premier
    #[Route('/commandes', name: 'app_admin_commandes')]
    public function liste(CommandeRepository $repo): Response  // ← plus de MessageRepository
    {
        $avecNonLus = $repo->findCommandesNonLues();  // ← $repo et non $msgRepo
        $avecNonLusIds = array_map(fn($c) => $c->getId(), $avecNonLus);
    
        $toutes = $repo->findBy([], ['dateCommande' => 'DESC']);
        $autresCommandes = array_filter(
            $toutes,
            fn($c) => !in_array($c->getId(), $avecNonLusIds)
        );
    
        return $this->render('admin/commande/liste.html.twig', [
            'commandes_nonlues' => $avecNonLus,
            'commandes'         => array_values($autresCommandes),
        ]);
    }

    // Détail commande + tchat admin
    #[Route('/commande/{id}/chat', name: 'app_admin_commande_chat')]
    public function chat(
        Commande $commande,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Marquer les messages client comme lus
        foreach ($commande->getMessages() as $message) {
            if (!$message->isAdmin() && !$message->isLu()) {
                $message->setLu(true);
            }
        }

        // Envoi d'un message admin
        if ($request->isMethod('POST')) {
            $contenu = trim($request->request->get('contenu', ''));
            if ($contenu !== '') {
                $message = new Message();
                $message->setCommande($commande);
                $message->setAuteur($this->getUser());
                $message->setContenu($contenu);
                $message->setIsAdmin(true);
                $em->persist($message);
                $em->flush();
        
                // ✅ Réponse JSON si appel AJAX
                if ($request->isXmlHttpRequest()) {
                    return new \Symfony\Component\HttpFoundation\JsonResponse([
                        'success'   => true,
                        'contenu'   => $message->getContenu(),
                        'dateEnvoi' => $message->getDateEnvoi()->format('d/m H:i'),
                        'isAdmin'   => true,
                    ]);
                }
            }
        }
        
        $em->flush();

        return $this->render('admin/commande/chat.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Modifier état (déjà existant)
    #[Route('/commande/{id}/etat', name: 'app_admin_commande_etat')]
    public function modifierEtat(Commande $commande, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EtatCommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'État mis à jour.');
            return $this->redirectToRoute('app_admin_commandes');
        }

        return $this->render('admin/commande/modifier-etat.html.twig', [
            'form'     => $form->createView(),
            'commande' => $commande,
        ]);
    }
}