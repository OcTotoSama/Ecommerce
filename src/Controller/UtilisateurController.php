<?php

namespace App\Controller;

use App\Controller\UtilisateurController;
use App\Entity\User;
use App\Form\ModifierUserType;
use App\Form\SupprimerUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UtilisateurController extends AbstractController
{
    #[Route('/liste-utilisateur', name: 'app_liste_utilisateur', methods: ['GET', 'POST'])]
    public function liste(Request $request, UserRepository $Repository,
        EntityManagerInterface $em): Response {
        $repositorys = $Repository->findAll();
        $actifs = $Repository->findBy(['isActive' => true]);
        $inactifs = $Repository->findBy(['isActive' => false]);

        $form = $this->createForm(SupprimerUserType::class, null, [
            'returns' => $repositorys,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selected = $form->get('returns')->getData();
            $ids = [];

            foreach ($selected as $element) {
                $ids[] = $element->getId();
                $em->remove($element);
            }

            $em->flush();

            //  JSON si AJAX
            if ($request->isXmlHttpRequest()) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => true,
                    'ids' => $ids,
                ]);
            }

            $this->addFlash('notice', 'Utilisateurs supprimés avec succès');
            return $this->redirectToRoute('app_liste_utilisateur');
        }

        return $this->render('admin/utilisateur/listeuser.html.twig', [
            'returns' => $repositorys,
            'form' => $form->createView(),
            'actifs' => $actifs,
            'inactifs' => $inactifs,
        ]);
    }

    #[Route('/modifier-utilisateur/{id}', name: 'app_modifier_utilisateur')]
    public function modifier(Request $request, User $var, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ModifierUserType::class, $var);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($var);
                $em->flush();

                $this->addFlash('notice', 'Utilisateur modifiée');

                return $this->redirectToRoute('app_liste_utilisateur');
            }
        }

        return $this->render('admin/utilisateur/modifier-utilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reactiver-utilisateur/{id}', name: 'app_reactiver_utilisateur')]
    public function reactiver(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsActive(true);
        $em->flush();

        $this->addFlash('success', 'Utilisateur réactivé');

        return $this->redirectToRoute('app_liste_utilisateur');
    }

    #[Route('/desactiver-utilisateur/{id}', name: 'app_desactiver_utilisateur')]
    public function desactiver(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsActive(false);
        $em->flush();

        $this->addFlash('success', 'Utilisateur désactivé');

        return $this->redirectToRoute('app_liste_utilisateur');
    }

    #[Route('/changer-role/{id}', name: 'app_changer_role')]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function changerRole(User $user, EntityManagerInterface $em, Request $request): Response
    {
        if (in_array('ROLE_PROPRIETAIRE', $user->getRoles())) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Impossible de modifier le rôle du propriétaire.']);
            }
            $this->addFlash('error', 'Impossible de modifier le rôle du propriétaire.');
            return $this->redirectToRoute('app_liste_utilisateur');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $user->setRoles([]);
            $newRole = 'user';
        } else {
            $user->setRoles(['ROLE_ADMIN']);
            $newRole = 'admin';
        }

        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true, 'newRole' => $newRole]);
        }

        return $this->redirectToRoute('app_liste_utilisateur');
    }



 /*   #[Route('/404', name: 'app_404')]
public function notFound(): Response
{
    return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
}*/
}
