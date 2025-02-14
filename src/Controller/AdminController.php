<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Users;
use App\Form\BOCategorieFormType;
use App\Form\BOUserEditFormType;
use App\Repository\CategorieRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name:'app_admin_home')]
    public function index() : Response 
    {
        return $this->render('admin/index.html.twig', [

        ]);
    }
}