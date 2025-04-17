<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    
    // recherche des produits par catégorie
    public function findByCategory(int $categorie){
        $qb = $this->createQueryBuilder('p')
        ->select('p.id, p.nom, p.description, p.prix, p.image')
        ->where('p.categorie = :categorie')
        ->setParameter('categorie', $categorie);

        return $qb->getQuery()->getResult();
    }

    public function findByCategoryAndFilter(Categorie $categorie, array $filtres)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.id, p.nom, p.description, p.prix, p.image')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
        ;

        $prixMin = $filtres['prix_minimum'];
        $prixMax = $filtres['prix_maximum'];
        $orderBy = $filtres['order'];
        $asc = $filtres['asc'];
        
        if ($prixMin !== null && $prixMin > 0) {
            $queryBuilder
                ->andWhere('p.prix > :prixMin')
                ->setParameter('prixMin', $prixMin)
            ;
        }
        if ($prixMax !== null && $prixMax < 5000) {       // valeur 5000 à changer avec le prix le plus haut qu'on aie
            $queryBuilder
                ->andWhere('p.prix < :prixMax')
                ->setParameter('prixMax', $prixMax)
            ;
        }
        if ($orderBy === 'prix' && $asc === 'true') {
            $queryBuilder
                ->orderBy('p.prix', 'ASC')
            ;
        } else if ($orderBy === 'prix' && $asc === 'false') {
            $queryBuilder
                ->orderBy('p.prix', 'DESC')
            ;
        }
        
        return $queryBuilder->getQuery()->getResult();
    }
}
