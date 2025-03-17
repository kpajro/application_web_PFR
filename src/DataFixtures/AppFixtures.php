<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Users;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

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
        private UserPasswordHasher $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $this->createUsers(100, $manager);              // création de 100 utilisateurs exemples
        $this->createCategories(10, $manager);          // création de 10 catégories exemples

        $manager->flush();
    }

    public function createUsers(int $amount, ObjectManager $m) : void
    {
        for ($i = 0; $i < $amount; $i++) {
            $user = new Users();

            $user->setEmail('testuser-' . $i . '@datafixtures.orm');
            $plainPassword = 'Test-Password-' . $i;
            $this->hasher->hashPassword($user, $plainPassword);
            $user->setAccountType(rand(1, 2));
            $user->setCountry('FR');

            $minCreationDate = strtotime('2024-01-01');
            $maxCreationDate = strtotime('2025-03-01');
            $creationDate = date('Y-m-d H:i:s', rand($minCreationDate, $maxCreationDate));
            $user->setCreatedAt(new DateTimeImmutable($creationDate));

            $minLogDate = date('Y-m-d H:i:s', $user->getCreatedAt());
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
    }

    public function createCategories(int $amount, ObjectManager $m) : void
    {
        for($i = 0; $i < $amount; $i++) {
            $categorie = new Categorie();
            $categorie->setNom('Catégorie de test ' . $i);
            
            $m->persist($categorie);
        }
    }
}
