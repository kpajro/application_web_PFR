<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Users;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\UsersRepository;
use App\Service\LoremIpsumGenerator;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 *
 *   Les fixures servent à ajouter des données de test au projet, ça va permettre de pouvoir avoir une idée des temps de chargements et tout ça et c'est aussi pratique pour le front; de se rendre compte de comment les choses rendent avec beaucoup de données dans la base
 *
 *   Pour les utiliser :
 *   - Assurez vous d'avoir la base de données à jour en faisant les dernières migrations : 
 *       -> php bin/console make:migration 
 *       -> php bin/console doctrine:migrations:migrate
 *   - Lancez la commande pour charger les fixtures dans la base : 
 *       -> php bin/console doctrine:fixtures:load
 *       !! à noter que lancer cette commande supprimera toutes les données pré existentes dans la bdd !!
 *   - Pour ajouter les données à celles que vous avez déjà dans votre base, vous pouvez ajouter --append :
 *       -> php bin/console doctrine:fixtures:load --append
 *   - Vous pouvez facilement changer la quantité d'objets créés en modifiant le premier paramètre de chaque fonction
 *   
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

    /**
     * fonction qui lance toutes les fixtures
     */
    public function load(ObjectManager $manager): void
    {
        dump('Début de la génération de données.');
        dump(' ');

        // pour chaque fonction, modifier le premier paramètre pour modifier le nombre d'utilisateurs à créer
        $this->createUsers(300, $manager);              // création de 300 utilisateurs exemples
        $this->createCategories(5, $manager);          // création de 10 catégories exemples
        $this->createProducts(600, $manager);          // création de 1500 produits exemples
        $this->createPaniers(500, $manager);            // création de 600 paniers exemples
        $this->createAvis(600, $manager);              // création de 1000 avis exemples

        dump(' ');
        dump('Toutes les données ont été générées.');
    }

    /**
     * fonction pour créer un nombre défini d'utilisateurs de test
     * @param int $amount La quantité d'objets à créer
     */
    public function createUsers(int $amount, ObjectManager $m) : void
    {
        dump('Génération des utilisateurs...');
        for ($i = 0; $i < $amount; $i++) {
            $user = new Users();

            $user->setEmail('testuser-' . $i . '@datafixtures.orm');
            $user->setName('TESTEUR' . $i);
            $user->setFirstname('Exemple');
            $plainPassword = 'Test-Password-' . $i;
            $user->setPassword($this->hasher->hashPassword($user, $plainPassword));     // le mdp est hashé avant d'être entré en bdd
            $user->setAccountType(rand(1, 2));      // 1 chance sur 2 d'être soi un compte entreprise, soit un compte particulier
            $user->setCountry('FR');

            $minCreationDate = strtotime('2024-01-01');
            $maxCreationDate = strtotime('2025-03-01');
            $creationDate = date('Y-m-d H:i:s', rand($minCreationDate, $maxCreationDate));
            $user->setCreatedAt(new DateTimeImmutable($creationDate));

            $minLogDate = strtotime($user->getCreatedAt()->format('Y-m-d H:i:s'));
            $maxLogDate = strtotime('2025-03-01');
            $LogDate = date('Y-m-d H:i:s', rand($minLogDate, $maxLogDate));     // date de derniere connexion entre la date de création du compte et le 1er mars 2025
            $user->setLastLogIn(new DateTimeImmutable($LogDate));

            $minBirthDate = strtotime('1970-01-01');
            $maxBirthDate = strtotime('2004-01-01');
            $birthDate = date('Y-m-d H:i:s', rand($minBirthDate, $maxBirthDate));
            $user->setBirthday(new DateTimeImmutable($birthDate));

            $phone = '+33 6 00000000';
            $phone = sprintf('%s%04d', substr($phone, 0, -4), rand(0, 9999));
            $user->setPhoneNumber($phone);

            $m->persist($user);
            
            dump('Utilisateur ' . $i . '/' . $amount);
        }

        $m->flush();
        dump('Utilisateurs générés.');
        dump('');
    }

    /**
     * fonction pour générer un nombre défini de catégories de test
     * @param int $amount La quantité d'objets à créer
     */
    public function createCategories(int $amount, ObjectManager $m) : void
    {
        dump('Génération des catégories...');
        for($i = 0; $i < $amount; $i++) {
            $categorie = new Categorie();
            $categorie->setNom('Catégorie de test ' . $i);
            $categorie->setNbProduits(0);

            $m->persist($categorie);
            dump('Catégorie ' . $i . '/' . $amount);
        }

        $m->flush();
        dump('Catégories générées.');
        dump(' ');
    }

    /**
     * fonction pour créer un nombre défini de produits de test
     * @param int $amount La quantité d'objets à créer
     */
    public function createProducts(int $amount, ObjectManager $m) : void
    {
        dump('Génétation des produits...');
        $editeurs = [];
        for ($j = 0; $j < 30; $j++) {
            $editeurs[$j] = 'Editeur ' . $j;    // création de 30 éditeurs de tests
        }

        // éléments communs à tous les produits pour faire plus simple
        $langages = ['FR', 'EN', 'ITA', 'GER', 'SPA'];
        $os = ['WIN', 'LIN', 'MacOS'];
        $priceDecimals = [0.5, 0.99, 0.25, 0, 0.9];
        $description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed dui tincidunt, aliquet dolor non, tempus ipsum.';
        $categories = $this->categorieRepo->findAll();

        for ($i = 0; $i < $amount; $i++) {
            $produit = new Produit();
            $produit->setActive(true);
            $produit->setNom('Produit Exemple ' . $i);
            $produit->setEditeur($editeurs[rand(0, 29)]);
            $produit->setPrix(rand(100, 3000) + $priceDecimals[array_rand($priceDecimals)]);        // prix entre 100 et 3000 + une terminaison au hasard prise dans l'array créé plus tôt
            $produit->setImages([]);

            $categorie = $categories[array_rand($categories)];
            $produit->setCategorie($categorie);
            $categorie->setNbProduits($categorie->getNbProduits() + 1);

            $produit->setDescription($description);
            $produit->setLongDescription($this->lorem->ipsum(8));

            $produit->setLangages($langages);
            $produit->setOs($os);

            $produit->setIsBulkSale(rand(0,3) == 1);        // 1/4 chance d'être vendu par lot
            $produit->setBulkSize($produit->isBulkSale() ? rand(10, 100) : null);
            $produit->setIsLimitedStock(rand(0,4) == 1);        // 1/5 chance d'être en stock limité
            $produit->setStock($produit->isLimitedStock() ? rand(100, 5000) : null);

            $m->persist($produit);
            $m->persist($categorie);
            dump('Produit ' . $i . '/' . $amount);
        }

        $m->flush();
        dump('Produits générés.');
        dump(' ');
    }

    /**
     * fonction pour créer un nombre défini de paniers de test
     * @param int $amount La quantité d'objets à créer
     */
    public function createPaniers(int $amount, ObjectManager $m) : void
    {
        dump('Génération des paniers...');
        $produits = $this->produitRepo->findAll();
        $users = $this->usersRepo->findAll();

        for ($i = 0; $i < $amount; $i++) {
            $user = rand(1, 3) == 1 ? null : $users[array_rand($users)];     // l'utilisateur lié au panier a 1/3 chance d'être anonyme
            $panier = new Panier($user);
            $panier->setEtat(rand(1, 3));

            $minCreateDate = $user !== null ? strtotime($user->getCreatedAt()->format('Y-m-d H:i:s')) : strtotime('2024-01-01');        // si le panier est lié à un utilisateur, date de création ne peut pas être inférieur à la date de création du compte utilisateur
            $maxCreateDate = strtotime('2025-03-01');
            $createDate = date('Y-m-d H:i:s', rand($minCreateDate, $maxCreateDate));
            $panier->setCreatedAt(new DateTimeImmutable($createDate));

            for ($j = 0; $j < rand(1, 5); $j++) {       // 1 à 5 produits au hasard sont ajoutés au panier
                $produit = $produits[array_rand($produits)];
                $panier->addProduit($produit, $m);
            }

            $m->persist($panier);
            dump('Panier ' . $i . '/' . $amount);
        }

        $m->flush();
        dump('Paniers générés.');
        dump(' ');
    }

    /**
     * fonction pour générer un nombre défini d'avis de test
     * @param int $amount La quantité d'objets à créer
     */
    public function createAvis(int $amount, ObjectManager $m) : void
    {
        dump('Génération des avis...');
        $produits = $this->produitRepo->findAll();
        $users = $this->usersRepo->findAll();
        $commentaire = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed dui tincidunt, aliquet dolor non, tempus ipsum.';      // le commentaire sera le même pour tous les avis afin de simplifier

        for ($i = 0; $i < $amount; $i++) {
            $avis = new Avis();
            $produit = $produits[array_rand($produits)];
            $user = $users[array_rand($users)];
            $minDate = strtotime($user->getCreatedAt()->format('Y-m-d H:i:s'));     // la date de post ne peut pas être inférieur à la date de création du compte utilisateur
            $maxDate = strtotime('2025-05-01');
            $date = date('Y-m-d H:i:s', rand($minDate, $maxDate));

            $avis->setUser($users[array_rand($users)]);
            $avis->setProduit($produit);
            $avis->setNote(rand(0, 50) / 10);
            $avis->setCommentaire($commentaire);
            $avis->setDate(new DateTime($date));
            $m->persist($avis);
            $produit->addAvis($avis);
            $produit->updateNote();

            $m->persist($produit);
            dump('Avis ' . $i . '/' . $amount);
        }

        $m->flush();
        dump('Avis générés.');
        dump(' ');
    }
}
