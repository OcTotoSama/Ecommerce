<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;


final class BaseController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(ProduitRepository $repository): Response
    {
        $produits = $repository->findAll();

        return $this->render('base/index.html.twig', [
            'produits' => $produits,
        ]);
    }
    #[Route('/redirect-access-denied', name: 'access_denied_redirect')]
    public function accessDenied(): Response
    {
        return $this->redirectToRoute('app_accueil');
    }

   
    public function recherche(): Response
    {
        return $this->render('base/recherche.html.twig');
    }

    #[Route('/resultat', name:'app_resultat')]
    public function resultat(Request $request, ProduitRepository $produitrepository ): Response
    {
        $produits=[];
        $word=null;
        if ($request->isMethod('GET')){
            if($request->get('search')){
                $word=$request->get('search');

                $produits=$produitrepository->recherche($word);
            }
        }


        return $this->render('base/resultat.html.twig', ["produits"=>$produits, "word"=>$word]);
    }

}
