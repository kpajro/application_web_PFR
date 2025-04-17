<?php

namespace App\Controller;
use App\Form\ContactFormType;
use App\Repository\ParametreSiteRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, ParametreSiteRepository $paramRepository): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $param = $paramRepository->find(1);
            $supportEmail = $param?->getAdresseSupport();

            $email = (new TemplatedEmail())
                ->from(new Address($data['email']))
                ->to($supportEmail)
                ->subject('NOMICI - Confirmation de votre demande')
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'adresse_mail' => $data['email'],
                    'message' => $data['message'],
                ]);

            $mailer->send($email);

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
