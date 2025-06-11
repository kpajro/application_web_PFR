<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\BOProduitFormType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * Controller qui gère toutes les routes de la gestion des produits dans le Back Office
 * - Requiert une connexion en tant qu'administrateur
 * - Toutes les routes ont pour préfixe '/admin/produits'
 */
#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class BOProduitsController extends AbstractController
{
    /**
     * Route qui liste tous les produits présents sur le site : '' ('app_admin_produits_list')
     * @return Response Template twig 'admin/produits/list.html.twig'
     */
    #[Route('', name: 'app_admin_produits_list')]
    public function produits(ProduitRepository $produitRepo) : Response
    {
        $produits = $produitRepo->findAll();

        return $this->render('admin/produits/list.html.twig', [
            'produits' => $produits,
        ]);
    }

    /**
     * Route pour créer un nouveau produit, destiné à une modale : '/create' ('app_admin_produits_create')
     * @return Response Template twig 'admin/produits/form.html.twig' (formulaire spécial utilisé pour la création et la modification de produits)
     */
    #[Route('/create', name: 'app_admin_produits_create')]
    public function produitCreate(Request $request, EntityManagerInterface $em, SluggerInterface $slugger) : Response
    {
        $produit = new Produit;
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_create')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // la gestion de l'upload de l'image se fait de manière séparée
            $imageData = [
                'icon' => $form->get('icon')->getData(),
                'imageMain' => $form->get('imageMain')->getData(),
                'imageOther' => $form->get('imageOther')->getData()
            ];
            $this->handleImages($imageData, $slugger, $produit);    // fonction pour la gestion des images
            $prCategorie = $produit->getCategorie();
            $prCategorie->setNbProduits($prCategorie->getNbProduits() + 1);     // on augmente le nombre de produits dans la catégorie de 1

            $em->persist($produit);
            $em->persist($prCategorie);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');       // redirection vers la liste des produits une fois le produits créé
        }

        return $this->render('admin/produits/form.html.twig', [
            'form' => $form,
            'title' => 'Création de produit',
            'btnAction' => 'Créer',
            'action' => 'ajout'     // précision pour la template twig de l'action désirée (ajout ou modification)
        ]);
    }

    /**
     * Route pour la modification d'un produit, déstiné à une modale : '/{id}/edit' ('app_admin_produits_edit)
     * @param Produit $produit Produit ciblé par le chemin de la route
     * @return Response Template Twig 'admin/produits/form.html.twig' (modale de formulaire utilisée pour la modification et la création de produits)
     */
    #[Route('/{id}/edit', name: 'app_admin_produits_edit')]
    public function produitEdit(Produit $produit, Request $request, EntityManagerInterface $em, SluggerInterface $slugger) : Response
    {
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_edit', ['id' => $produit->getId()])
        ]);
        $form->handleRequest($request);
        $directory = 'uploadedFiles/produitImages/' . $slugger->slug($produit->getCategorie()->getNom()) . '/';     // répertoire dans lequel les images du produit existantes se trouvent

        if ($form->isSubmitted() && $form->isValid()) {
            // gestion de l'upload d'images séparées
            $imageData = [
                'icon' => $form->get('icon')->getData(),
                'imageMain' => $form->get('imageMain')->getData(),
                'imageOther' => $form->get('imageOther')->getData()
            ];
            
            $this->handleImages($imageData, $slugger, $produit);    // fonction pour la gestion des images
            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');       // redirection vers la liste des produits une fois la modification terminée
        }

        return $this->render('admin/produits/form.html.twig', [
            'form' => $form,
            'action' => 'modif',    // type d'action à envoyer au template twig (modification ou ajout de produit)
            'btnAction' => 'Enregistrer',
            'produit' => $produit,
            'directory' => $directory,
            'deletable' => true,        // la suppression est activée
            'deleteAction' => 'Supprimer le produit',
            'deleteWarning' => 'Êtes-vous sûr(e) de vouloir supprimer "' . $produit->getNom() . '" ? Cette action est irréversible.',   // message de confirmation de suppression
            'deleteLink' => $this->generateUrl('app_admin_produits_delete', ['id' => $produit->getId()])    // lien vers la suppression du produit
        ]);
    }

    /**
     * Route qui gère la suppression d'un produit : '/{id}/delete' ('app_admin_produits_delete')
     * @param Produit $produit Produit visé par le chemin de la route
     * @return Response Redirection vers la liste des produits
     */
    #[Route('/{id}/delete', name:'app_admin_produits_delete')]
    public function produitDelete(Produit $produit, EntityManagerInterface $em) : Response
    {
        $prCategorie = $produit->getCategorie();
        $prCategorie->setNbProduits($prCategorie->getNbProduits() - 1);     // Le nombre de produits dans la catégorie est réduit de 1

        $em->persist($prCategorie);
        $em->remove($produit);
        $em->flush();

        return $this->redirectToRoute('app_admin_produits_list');
    }

    /**
     * Fonction pour le traitement d'uplaod d'images
     * @param array $images Array des images qui viennent d'être uploadées via le formulaire
     * @param SluggerInterface $slugger Nécessaire pour slugger les titres pour la nomenclature des fichiers
     * @param Produit $produit Produit cible pour la gestion des images
     */
    public function handleImages(array $images, SluggerInterface $slugger, Produit $produit)
    {
        $directory = 'uploadedFiles/produitImages/' . $slugger->slug($produit->getCategorie()->getNom()) . '/';     // le repertoire dans lequel se trouvent/se trouveront les images
        $previousImages = $produit->getImages();    // on récupère les images déjà existantes (s'il y en a)
        $produitImages = [];    // array d'images qui va être mis en bdd

        // si le produit n'avait pas d'images du tout (même pas un array vide), on lui donne l'array $produitImages
        // (cas exceptionnel, normalement tous les produits on au moins un array vide dans le champ Images)
        if ($produit->getImages() === null) {
            $previousImages = $produitImages;   
        }
        if ($images['icon']) {  // cas pour l'image uploadée dans le champ 'icone'
            /** @var UploadedFile $icon **/
            $icon = $images['icon'];
            $newIconName = $slugger->slug($produit->getNom()) . '-ICON.' . $icon->guessExtension();
            $icon->move($directory, $newIconName);
            $produitImages['icon'] = $newIconName;  // on ajoute l'image au futur array d'images avec la clé 'icon'
        } else if (key_exists('icon', $previousImages) && $previousImages['icon'] && !$images['icon']) {        // si une icone existait déjà et que pas de nouvelle icone a été uploadée
            $produitImages['icon'] = $previousImages['icon'];
        }
        if ($images['imageMain']) {     // cas pour l'image uploadée dans le champ 'main'
            /** @var UploadedFile $main */
            $main = $images['imageMain'];
            $newMainName = $slugger->slug($produit->getNom()) . '-MAIN.' . $main->guessExtension();
            $main->move($directory, $newMainName);
            $produitImages['main'] = $newMainName;  // on ajoute l'image au futur array d'images avec la clé 'main'
        } else if (key_exists('main', $previousImages) &&$previousImages['main'] && !$images['imageMain']) {    // si une image principale existait déjà et que pas de nouvelle image principale
            $produitImages['main'] = $previousImages['main'];
        }
        if ($images['imageOther']) {    // toutes les autres images
            $others = [];   // array qui sera sous la clé 'other'
            $i = 1;
            foreach ($images['imageOther'] as $other) { // il peut y avoir plusieurs images dans ce champ donc on les traite toutes
                /** @var UploadedFile $main */
                $newOtherName = $slugger->slug($produit->getNom()) . '-OTHER' . $i . '.' . $other->guessExtension();
                $other->move($directory, $newOtherName);
                $others[] = $newOtherName;
                $i++;
            }
            $produitImages['other'] = $others;  // on ajoute l'array d'images au futur array d'images (du produit) sous la clé 'other'
        } else if (key_exists('other', $previousImages) && $previousImages['other'] && !$images['imageOther']) {    // si des images étaient déjà présentes et qu'on en a pas upload de nouvelles
            $produitImages['other'] = $previousImages['other'];
        }
        
        $produit->setImages($produitImages);    // l'array $produitImages est ajouté au produit, ainsi le produit aura un array vide s'il n'a aucune image
    }
}