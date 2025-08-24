<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST', 'OPTIONS'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if(!$data){
            return new JsonResponse([
                'message' => 'Error encountered while registering!',
            ]);
        }
        $user = new Users();
        $user->setFirstname($data['firstname'] ?? '');
        $user->setName($data['lastname'] ?? '');
        $user->setEmail($data['email']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setBirthday(new \DateTimeImmutable());
        

        $plainPassword = $data['password'];
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

        if (isset($data['phoneNumber'])) {
            $user->setPhoneNumber($data['phoneNumber']);
        }
        if (isset($data['country'])) {
            $user->setCountry($data['country']);
        }
        if (isset($data['accountType'])) {
            $user->setAccountType(1);
        }
           
        $panier = new Panier($user);
        $user->addPanier($panier);
        $user->setPanierActif($panier);

        $em->persist($panier);
        $em->persist($user);
        $em->flush();
        
        return new JsonResponse([
            'message' => 'User registered!',
        ]);
    }
}