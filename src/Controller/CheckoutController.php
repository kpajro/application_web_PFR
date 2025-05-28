<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Service\PanierHandler;
use App\Repository\PanierRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CheckoutController extends AbstractController
{
    public function __construct(private PanierHandler $panierHandler)
    {
    }

    #[Route('/retrieve-payment', name: 'app_stripe_payment_retrieve', methods: ['POST'])]
    public function retrievePayment(Request $request): JsonResponse
    {
        \Stripe\Stripe::setApiKey('rk_test_51RSvQkRVumHN60oofb9knkAD9MuBQ3D4n4BT6IbUeQa8gB2qeOR1figukukv9QfbrAUMutzFrTLPCjx6A5HvSxqU00h6rMO9H4');
        // A finir pour recupération du paiement et l'insérer dans la base de données
        $paymentIntent = \Stripe\PaymentIntent::retrieve('');
        Return new JsonResponse($paymentIntent);
    }

    #[Route('/create-checkout', name: 'app_stripe_checkout_create', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        // temporaire pour les tests (ou sinon osef)
        \Stripe\Stripe::setApiKey('rk_test_51RSvQkRVumHN60oo3B0poNqglD7FJgwbKRuRrVSsbf1TAuXMQHY7QJTXxjnmblJf7Do95CZSBlUt92qMiIusTwPX00uMhWtTe7');

        $panier = $this->panierHandler->getActivePanier($this->getUser(), $request);
        //~#Klaudiusz vvv pourquoi je dois faire ca pour initialiser le panier ???
        $this->panierHandler->getPanierTotalPrice($panier);
        $panierProduits = $panier->getPanierProduits();

        $lineItems = [];

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

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/checkout', name: 'app_stripe_checkout')]
    public function checkoutPage(Request $request): Response
    {
        // temporaire pour les tests (ou sinon osef)
        return $this->render('/checkout/checkout.html.twig', [
            'stripe_public_key' => 'pk_test_51RSvQkRVumHN60ooKlCL6qUPaVblzy3dtuAP3XwdF8LChY4G56VLJKpi526WBpi3VUEy0XcJifynKetmnul5Us7100AS1ThEJH',     
        ]);
    }
    #[Route('/payment-success', name: 'app_stripe_payment_success')]
    public function paymentSuccess(): Response
    {
        return $this->render('checkout/success.html.twig');
    }

    #[Route('/payment-cancel', name: 'app_stripe_payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('checkout/cancel.html.twig');
    }
}
