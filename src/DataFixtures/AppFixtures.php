<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\PanierProduits;
use App\Entity\Produit;
use App\Entity\Users;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\UsersRepository;
use App\Service\LoremIpsumGenerator;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/*

    Les fixures servent à ajouter des données de test au projet, ça va permettre de pouvoir avoir une idée des temps de chargements et tout ça et c'est aussi pratique pour le front; de se rendre compte de comment les choses rendent avec beaucoup de données dans la base

    Pour les utiliser :
    - Assurez vous d'avoir la base de données à jour en faisant les dernières migrations : 
        -> php bin/console make:migration 
        -> php bin/console doctrine:migrations:migrate
    - Lancez la commande pour charger les fixtures dans la base : 
        -> php bin/console doctrine:fixtures:load
        !! à noter que lancer cette commande supprimera toutes les données pré existentes dans la bdd !!
    - Pour ajouter les données à celles que vous avez déjà dans votre base, vous pouvez ajouter --append :
        -> php bin/console doctrine:fixtures:load --append
    - Vous pouvez facilement changer la quantité d'objets créés en modifiant le premier paramètre de chaque fonction
    
*/

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private CategorieRepository $categorieRepo,
        private ProduitRepository $produitRepo,
        private UsersRepository $usersRepo,
        private LoremIpsumGenerator $lorem,
    ) {}

    public function load(ObjectManager $manager): void
    {
        dump('Début de la génération de données.\n');
        $this->createUsers(100, $manager);              // création de 100 utilisateurs exemples
        $this->createCategories(10, $manager);          // création de 10 catégories exemples
        $this->createProducts(750, $manager);           // création de 750 produits exemples
        $this->createPaniers(400, $manager);            // création de 400 paniers exemples
        dump('Toutes les données ont été générées.\n');
    }

    public function createUsers(int $amount, ObjectManager $m) : void
    {
        for ($i = 0; $i < $amount; $i++) {
            $user = new Users();

            $user->setEmail('testuser-' . $i . '@datafixtures.orm');
            $user->setName('TESTEUR' . $i);
            $user->setFirstname('Exemple');
            $plainPassword = 'Test-Password-' . $i;
            $user->setPassword($this->hasher->hashPassword($user, $plainPassword));
            $user->setAccountType(rand(1, 2));
            $user->setCountry('FR');

            $minCreationDate = strtotime('2024-01-01');
            $maxCreationDate = strtotime('2025-03-01');
            $creationDate = date('Y-m-d H:i:s', rand($minCreationDate, $maxCreationDate));
            $user->setCreatedAt(new DateTimeImmutable($creationDate));

            $minLogDate = strtotime($user->getCreatedAt()->format('Y-m-d H:i:s'));
            $maxLogDate = strtotime('2025-03-01');
            $LogDate = date('Y-m-d H:i:s', rand($minLogDate, $maxLogDate));
            $user->setLastLogIn(new DateTimeImmutable($LogDate));

            $minBirthDate = strtotime('1970-01-01');
            $maxBirthDate = strtotime('2004-01-01');
            $birthDate = date('Y-m-d H:i:s', rand($minBirthDate, $maxBirthDate));
            $user->setBirthday(new DateTimeImmutable($birthDate));

            $phone = '+33 6 00000000';
            $phone = sprintf('%s%04d', substr($phone, 0, -4), rand(0, 9999));
            $user->setPhoneNumber($phone);

            $m->persist($user);
        }

        $m->flush();
        dump('Utilisateurs générés.');
    }

    public function createCategories(int $amount, ObjectManager $m) : void
    {
        for($i = 0; $i < $amount; $i++) {
            $categorie = new Categorie();
            $categorie->setNom('Catégorie de test ' . $i);
            $categorie->setNbProduits(0);

            $m->persist($categorie);
        }

        $m->flush();
        dump('Catégories générées.');
    }

    public function createProducts(int $amount, ObjectManager $m) : void
    {
        $editeurs = [];
        for ($j = 0; $j < 30; $j++) {
            $editeurs[$j] = 'Editeur ' . $j;
        }

        $langages = ['FR', 'EN', 'ITA', 'GER', 'SPA'];
        $os = ['WIN', 'LIN', 'MacOS'];
        $priceDecimals = [0.5, 0.99, 0.25, 0, 0.9];
        $description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed dui tincidunt, aliquet dolor non, tempus ipsum.';
        $categories = $this->categorieRepo->findAll();

        for ($i = 0; $i < $amount; $i++) {
            $produit = new Produit();
            $produit->setNom('Produit Exemple ' . $i);
            $produit->setEditeur($editeurs[rand(0, 29)]);
            $produit->setPrix(rand(100, 3000) + $priceDecimals[array_rand($priceDecimals)]);

            $categorie = $categories[array_rand($categories)];
            $produit->setCategorie($categorie);
            $categorie->setNbProduits($categorie->getNbProduits() + 1);

            $produit->setDescription($description);
            $produit->setLongDescription($this->lorem->ipsum(8));

            $produit->setLangages($langages);
            $produit->setOs($os);

            $produit->setIsBulkSale(rand(0,1) == 1);
            $produit->setBulkSize($produit->isBulkSale() ? rand(50, 500) : null);
            $produit->setIsLimitedStock(rand(0,1) == 1);
            $produit->setStock($produit->isLimitedStock() ? rand(500, 5000) : null);

            $m->persist($produit);
            $m->persist($categorie);
        }

        $m->flush();
        dump('Produits générés.');
    }

    public function createPaniers(int $amount, ObjectManager $m) : void
    {
        $produits = $this->produitRepo->findAll();
        $users = $this->usersRepo->findAll();

        for ($i = 0; $i < $amount; $i++) {
            $user = rand(1, 3) == 1 ? null : $users[array_rand($users)];     // l'utilisateur lié au panier a 1/3 chance d'être anonyme
            $panier = new Panier($user);
            $panier->setEtat(rand(1, 3));

            $minCreateDate = $user !== null ? strtotime($user->getCreatedAt()->format('Y-m-d H:i:s')) : strtotime('2024-01-01');
            $maxCreateDate = strtotime('2025-03-01');
            $createDate = date('Y-m-d H:i:s', rand($minCreateDate, $maxCreateDate));
            $panier->setCreatedAt(new DateTimeImmutable($createDate));

            for ($j = 0; $j < rand(1, 5); $j++) {
                $produit = $produits[array_rand($produits)];
                $panier->addProduit($produit, $m);
            }

            $m->persist($panier);
        }

        $m->flush();
        dump('Paniers générés.');
    }
}
