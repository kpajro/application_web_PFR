<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\BOProduitFormType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/produits')]
#[IsGranted('ROLE_ADMIN')]
class BOProduitsController extends AbstractController
{
    #[Route('', name: 'app_admin_produits_list')]
    public function produits(ProduitRepository $produitRepo) : Response
    {
        $produits = $produitRepo->findAll();

        return $this->render('admin/produits/list.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/create', name: 'app_admin_produits_create')]
    public function produitCreate(Request $request, EntityManagerInterface $em, SluggerInterface $slugger) : Response
    {
        $produit = new Produit;
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_create')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            if ($images) {
                $this->handleImages($images, $slugger, $produit);
            }

            $prCategorie = $produit->getCategorie();
            $prCategorie->setNbProduits($prCategorie->getNbProduits() + 1);

            $em->persist($produit);
            $em->persist($prCategorie);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');
        }

        return $this->render('admin/produits/form.html.twig', [
            'form' => $form,
            'title' => 'Création de produit',
            'btnAction' => 'Créer',
            'action' => 'ajout'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_produits_edit')]
    public function produitEdit(Produit $produit, Request $request, EntityManagerInterface $em, SluggerInterface $slugger) : Response
    {
        $form = $this->createForm(BOProduitFormType::class, $produit, [
            'action' => $this->generateUrl('app_admin_produits_edit', ['id' => $produit->getId()])
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image) {
                $this->handleImages($image, $slugger, $produit);
            }

            $em->persist($produit);
            $em->flush();

            return $this->redirectToRoute('app_admin_produits_list');
        }

        return $this->render('admin/produits/form.html.twig', [
            'form' => $form,
            'action' => 'modif',
            'btnAction' => 'Enregistrer',
            'produit' => $produit,
            'deletable' => true,
            'deleteAction' => 'Supprimer le produit',
            'deleteWarning' => 'Êtes-vous sûr(e) de vouloir supprimer "' . $produit->getNom() . '" ? Cette action est irréversible.',
            'deleteLink' => $this->generateUrl('app_admin_produits_delete', ['id' => $produit->getId()])
        ]);
    }

    #[Route('/{id}/delete', name:'app_admin_produits_delete')]
    public function produitDelete(Produit $produit, EntityManagerInterface $em) : Response
    {
        $prCategorie = $produit->getCategorie();
        $prCategorie->setNbProduits($prCategorie->getNbProduits() - 1);

        $em->persist($prCategorie);
        $em->remove($produit);
        $em->flush();

        return $this->redirectToRoute('app_admin_produits_list');
    }

    public function handleImages(array $images, SluggerInterface $slugger, Produit $produit)
    {
        $filesystem = new Filesystem();
        $timestamp = date('YmdHis');
        $imageNames = [];
        foreach ($images as $image) {
            $cleanFileName = $slugger->slug($produit->getNom()) . '_' . $timestamp . '.' . $image->guessExtension();

            try {
                $image->move('uploadedFiles/productImages/' . $slugger->slug($produit->getCategorie()->getNom()) . '/', $cleanFileName);
            } catch (FileException $e) {

            }
            $imageNames[] = $cleanFileName;
        }

        $produit->setImages($imageNames);
    }
}