<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class UtilisateurController extends AbstractController
{
    #[Route('/liste-utilisateur', name: 'app_liste_utilisateur')]
    public function index( UserRepository $Repository): Response
    {
        $varS= $Repository->findAll();
        return $this->render('utilisateur/listeuser.html.twig', [
            'returns' => $varS
        ]);
    }
}
