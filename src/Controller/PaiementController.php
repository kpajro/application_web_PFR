<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Users;
use App\Entity\Paiement;
use App\Service\PanierHandler;
use App\Repository\PaiementRepository;
use App\Repository\PanierRepository;
use App\Form\PaiementFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class PaiementController extends AbstractController
{

    /**
     * Constructeur du Handler du panier
     */
    public function __construct(private PanierHandler $panierHandler)
    {
    }

    /**
     * Route de création d'une session Stripe pour le paiement
     */
    #[Route('/create-checkout', name: 'app_stripe_checkout_create', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        // temporaire pour les tests
        \Stripe\Stripe::setApiKey('rk_test_51RSvQkRVumHN60oo3B0poNqglD7FJgwbKRuRrVSsbf1TAuXMQHY7QJTXxjnmblJf7Do95CZSBlUt92qMiIusTwPX00uMhWtTe7');

        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        // ~#Klaudiusz vvv pourquoi je dois faire ca pour initialiser le panier ???
        $this->panierHandler->getPanierTotalPrice($panier);
        $panierProduits = $panier->getPanierProduits();

        $lineItems = [];

        // Remplissage de la liste $lineItems avec les produits du panier produit
        foreach($panierProduits as $panierProduit){
            $produit = $panierProduit->getProduit();
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $produit->getPrix() * 100,
                    'product_data' => [
                        'name' => $produit->getNom(),
                    ],
                ],
                'quantity' => $panierProduit->getAmount(),
            ];
        }

        // Création d'une nouvelle session Stripe et définition des paramètres de paiements
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_stripe_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $session->id]); // renvoi de la réponse en json Symfony
    }

    /**
     * Route non utilisée pour l'instant pour récuperer l'addresse pour la facturation
     */
    #[Route('/pre-checkout', name: 'app_checkout_address')]
    public function checkoutAddress(Request $request): Response
    {
        $form = $this->createForm(PaiementFormType::class); // création d'un formulaire de paiement
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->set('billing_data', $data);
            return $this->redirectToRoute('app_stripe_checkout_create');
        }

        return $this->render('paiement/address_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * Route de la page Paiements pour retourner tous les paiements de l'utilisateur
     */
    #[Route('/paiements', name: 'app_paiements')]
    public function paiementsPage(PaiementRepository $paiementRepository): Response
    {
        $user = $this->getUser();

        $paiements = $paiementRepository->findBy(['user' => $user]);
        return $this->render('/paiement/list.html.twig', [
            'paiements' => $paiements,
        ]);
    }

    /**
     * Route du paiement quand il a succédé par Stripe, créer un paiement et l'ajoute à la base de données
     */
    #[Route('/payment-success', name: 'app_stripe_payment_success')]
    public function paymentSuccess(Request $request, EntityManagerInterface $em): Response
    {
        $loggedUser = $this->getUser();
        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request); // récupération du panier actif de l'utilisateur

        if(!$loggedUser){
            return $this->redirectToRoute('app_index');
        }
        $sessionId = $request->query->get("session_id");
        
        if (!$sessionId) {
            $this->addFlash('error', 'session de paiement introuvable');
            return $this->redirectToRoute('app_index'); // redirection vers la page index s'il n'y a pas de session de paiement
        }
        \Stripe\Stripe::setApiKey("rk_test_51RSvQkRVumHN60oo3B0poNqglD7FJgwbKRuRrVSsbf1TAuXMQHY7QJTXxjnmblJf7Do95CZSBlUt92qMiIusTwPX00uMhWtTe7");
        $session = \Stripe\Checkout\Session::retrieve($sessionId); // récupération de la session de paiement Stripe
        $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent); // récupération du paiement Stripe
        
        $existingpaiement = $em->getRepository(Paiement::class)->findOneBy(['uuid' => $paymentIntent->id]);
        if ($existingpaiement) {
            $this->addFlash('info', 'le paiement existe déjà'); 
            return $this->redirectToRoute('app_index'); // vérification si le paiement existe déjà pour ne pas mettre de paiements doublons
        }
        
        $paiement = new Paiement($loggedUser); // création du nouveau paiement
        $uuid = $paymentIntent->id;
        $montant = $paymentIntent->amount / 100;
        $date = (new \DateTime())->setTimestamp($paymentIntent->created);
        $status = $paymentIntent->status;
        
        //$billingData = $request->getSession()->get('billing_data');

        $paiement->setUuid($uuid);
        $paiement->setMontant($montant);
        $paiement->setDate($date);
        $paiement->setStatus($status);
        $paiement->setUserId($loggedUser);
        $panier->setEtat(2);

        $em->persist($paiement);
        $em->flush();
        
        return $this->render('paiement/success.html.twig');
    }
    /**
     * Route du paiement quand il échoue
     */
    #[Route('/payment-cancel', name: 'app_stripe_payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('paiement/cancel.html.twig');
    }
}
