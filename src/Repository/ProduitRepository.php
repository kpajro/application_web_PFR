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
        ->select('p.id, p.nom, p.description, p.prix')
        ->where('p.categorie = :categorie')
        ->setParameter('categorie', $categorie);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fonction pour rechercher les produits d'une catégorie donnée selon un array de filtres
     * - Renvoie l'id, le nom, la descrition courte, le prix, les os, les langages, l'editeur, les images, la note, et l'état du produit
     * - les filtres possibles sont:
     *      - prix minimum ['prix_minimum']
     *      - prix maximum ['prix_maximum']
     *      - ordre de tri ['order']
     *      - croissant ou decroissant ['asc'] (si 1 -> croissant sinon décroissant)
     *      - une recherche par nom ['recherche']
     * @param Categorie $categorie Categorie ciblée
     * @param array $filtres Array de filtres à formatter correctement
     * @return Produits[] Array de produits
     */
    public function findByCategoryAndFilter(Categorie $categorie, array $filtres) : array
    {
        // query initiale
        // si aucun filtre c'est ce qui est renvoyé par la fonction
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p.id, p.nom, p.description, p.prix, p.os, p.langages, p.editeur, p.images, p.note, p.active')
            ->where('p.categorie = :categorie')
            ->setParameter('categorie', $categorie)
        ;

        // formattage des filtres
        $prixMin = $filtres['prix_minimum'];
        $prixMax = $filtres['prix_maximum'];
        $orderBy = $filtres['order'];
        $asc = $filtres['asc'];
        $recherche = $filtres['recherche'];
      /*   $os = $filtres['os'];
        $language = $filtres['langages'];
        $editor = $filtres['editor']; */
        
        // pour chaque filtre, on vérifie si le filtre est actif
        // s'il est actif, le filtre est ajouté à la query
        // sinon la fonction continue
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
        if ($orderBy === 'prix' && $asc !== '') {
            $queryBuilder
            ->orderBy('p.prix', $asc === '1' ? 'ASC' : 'DESC')
            ;
        }
        if ($orderBy === 'alpha' && $asc !== '') {
            $queryBuilder
            ->orderBy('p.nom', $asc === '1' ? 'ASC' : 'DESC')
            ;
        }
        if (!empty($recherche)){
            $queryBuilder
            ->andWhere('LOWER(p.nom) LIKE :rech')
            ->setParameter('rech', '%' . strtolower($recherche) . '%')
            ;
        }
       /*  if (!empty($os)) {
            $queryBuilder
            ->andWhere('p.os IN (:os)')
            ->setParameter('os', $os);
        }
    
        if (!empty($language)) {
            $queryBuilder
                ->andWhere('p.language IN (:langages)')
                ->setParameter('language', $language);
        }
    
        if (!empty($editor)) {
            $queryBuilder
                ->andWhere('p.editor = :editor')
                ->setParameter('editor', $editor);
        } */
        
        return $queryBuilder->getQuery()->getResult();
    }
}
