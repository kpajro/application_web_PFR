<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Produit;
use App\Form\AvisFormType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Controller qui prend en charge toutes les routes des fonctionnalités de la page produit
 */
class ProduitController extends AbstractController
{
    /**
     * Route de l'affichage de la d'un produit : '/produit/{id}/page-produit' ('app_produit_view')
     * @param Produit $produit Le produit ciblé par le chemin de la route
     * @return Response Template twig 'produit/view.html.twig'
     */
    #[Route('/produit/{id}/page-produit', name: 'app_produit_view')]
    public function viewProduit(Produit $produit, SluggerInterface $slugger) : Response
    {
        // on vérifie que le produit est actif, s'il est desactivé, seuls les administrateurs peuvent accéder à la page produit
        if (!$produit->isActive() && !array_search("ROLE_ADMIN", $this->getUser()->getRoles())) {       
            throw new AccessDeniedException('Produit non accessible');
        }

        $directory = '/uploadedFiles/produitImages/' . $slugger->slug($produit->getCategorie()->getNom()) . '/';        // on envoie le nom du repertoire dans lequel se trouvent les images au template twig
        $main = null;
        if (key_exists('main', $produit->getImages())) {        // on vérifie qu'une image 'main' existe
            $main = $directory . $produit->getImages()['main'];
        }

        return $this->render('produit/view.html.twig', [
            'produit' => $produit,
            'main' => $main,
            'directory' => $directory
        ]);
    }

    /**
     * Route pour laisser un avis sur un produit, requiert d'être connecté, déstiné à être dans une modale : '/produit/{id}/donner-avis' ('app_produit_donner_avis')
     * @param Produit $produit Le produit ciblé par le chemin de la route
     * @return Response Template twig '/produit/donner-avis.html.twig'
     */
    #[Route('/produit/{id}/donner-avis', name: 'app_produit_donner_avis')]
    public function donnerAvis(Produit $produit, Request $request, EntityManagerInterface $em): Response
    {
        // on vérifie que l'utilisateur est connecté à son compte
        if (!$this->getUser()) {
            return new Response('Utilisateur non connecté', 403);
        }
        
        $avis = new Avis();
        $form = $this->createForm(AvisFormType::class, $avis, [
            'action' => $this->generateUrl('app_produit_donner_avis', ['id' => $produit->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setDate(new DateTime());
            $avis->setProduit($produit);
            $avis->setUser($this->getUser());
            $produit->addAvis($avis);
            $produit->updateNote();
        
            $em->persist($avis);
            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('app_produit_view', ['id' => $produit->getId()]);     // redirection vers la page produit
        }

        return $this->render('/produit/donner-avis.html.twig', [
            'produit' => $produit,
            'form' => $form
        ]);
    }

    /**
     * Route pour voir les images d'un produit en grand, destiné à être dans une modale : '/produit/{id}/images' ('app_produit_images')
     * @param Produit $produit Produit ciblé par le chemin de la route
     * @return Response Template twig 'produit/images.html.twig'
     */
    #[Route('/produit/{id}/images', name:'app_produit_images')]
    public function produitImages(Produit $produit, SluggerInterface $slugger): Response 
    {
        $directory = '/uploadedFiles/produitImages/' . $slugger->slug($produit->getCategorie()->getNom()) . '/';
        $main = null;
        if (key_exists('main', $produit->getImages())) {
            $main = $directory . $produit->getImages()['main'];
        }

        return $this->render('produit/images.html.twig', [
            'images' => $produit->getImages(),
            'directory' => $directory,
            'main' => $main,
        ]);
    }

    /**
     * Route pour supprimer un avis, accessible uniquement par un compte administrateur ou par l'utilisateur ayant posté l'avis : '/produit/supprimer-avis/{avis}' ('app_produit_supprimer_avis')
     * @param Avis $avis Avis ciblé par le chemin de la route
     * @return Response Redirection vers la page produit
     */
    #[Route('/produit/supprimer-avis/{avis}', name:'app_produit_supprimer_avis')]
    public function removeAvis(Avis $avis, EntityManagerInterface $em) : Response 
    {
        // on vérifie que l'utilisateur connecté est le même que le créateur de l'avis ou qu'il est administrateur
        $user = $this->getUser();
        if (!$user || $user !== $avis->getUser() || !array_search('ROLE_ADMIN', $user->getRoles())) {
            return new Response('Compte utilisateur invalide', 403);
        }
        $produit = $avis->getProduit();
        $produit->removeAvis($avis);
        $em->remove($avis);
        $produit->updateNote();
        $em->persist($produit);

        $em->flush();

        return $this->redirectToRoute('app_produit_view', ['id' => $produit->getId()]);
    }
}