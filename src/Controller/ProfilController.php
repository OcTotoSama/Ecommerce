<?php
namespace App\Controller;

use App\Form\ProfilInfoType;
use App\Form\ProfilPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();

        // ── Formulaire infos personnelles ──
        $formInfo = $this->createForm(ProfilInfoType::class, $user);
        $formInfo->handleRequest($request);

        if ($formInfo->isSubmitted() && $formInfo->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Informations mises à jour.');
            return $this->redirectToRoute('app_profil');
        }

        // ── Formulaire mot de passe ──
        $formPassword = $this->createForm(ProfilPasswordType::class);
        $formPassword->handleRequest($request);

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $ancienPassword  = $formPassword->get('ancienPassword')->getData();
            $nouveauPassword = $formPassword->get('nouveauPassword')->getData();

            if (!$hasher->isPasswordValid($user, $ancienPassword)) {
                $this->addFlash('danger', 'L\'ancien mot de passe est incorrect.');
            } else {
                $user->setPassword($hasher->hashPassword($user, $nouveauPassword));
                $em->flush();
                $this->addFlash('success', 'Mot de passe modifié avec succès.');
                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('profil/index.html.twig', [
            'formInfo'     => $formInfo->createView(),
            'formPassword' => $formPassword->createView(),
        ]);
    }

    // ── Désactivation du compte ──
    #[Route('/profil/supprimer', name: 'app_profil_supprimer', methods: ['POST'])]
    public function supprimer(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        Request $request
    ): Response {
        // Protection CSRF
        if (!$this->isCsrfTokenValid('supprimer_compte', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $user->setIsActive(false);
        $em->flush();

        // Déconnecter l'utilisateur
        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été désactivé.');
        return $this->redirectToRoute('app_accueil');
    }
}