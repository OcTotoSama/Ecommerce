<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Form\AjoutProduitType;  // tu le trouveras dans le form
use App\Entity\Produit; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;



final class AdminController extends AbstractController
{

    #[Route('/admin-ajout', name: 'app_admin_ajout')]
    public function AjoutProduit(Request $request, EntityManagerInterface $em): Response
    {
        $AjoutProduit = new Produit(); /*avec la première lettre en majuscule pour le deuxième nom*/
        $form = $this->createForm(AjoutProduitType::class,$AjoutProduit);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){
                $em->persist($AjoutProduit);
                $em->flush();
                $this->addFlash('notice','Formulaire envoyé');
                return $this->redirectToRoute('app_admin_ajout');
            }
        }
        return $this->render('admin/ajout_produit.html.twig', [
            'form' => $form->createView()
        ])
        
        ;
    }

}
