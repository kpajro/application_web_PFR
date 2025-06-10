<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestMailController extends AbstractController
{
    #[Route('/test-mailjet', name: 'test_mailjet')]
public function testMail(MailerInterface $mailer): Response
{
    try {
        $email = (new Email())
    ->from('sofiane.boudet1@gmail.com')
    ->to('sosuker@gmail.com')
    ->subject('Test Mailjet - reset password')
    ->text('Ceci est un test simple sans template.');

        $mailer->send($email);
        return new Response('Email envoyé avec succès.');
    } catch (\Exception $e) {
        return new Response('Erreur lors de l\'envoi du mail : ' . $e->getMessage());
    }
}
}
