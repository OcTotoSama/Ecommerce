<?php

namespace App\Repository;

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

    /**
     * @return Produit[] Returns an array of Produit objects
     */
    public function recherche($value): array
    {
        $qb =  $this->createQueryBuilder('p');
            $qb->andWhere($qb->expr()->like('p.name', ':val'))
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('p.name', 'ASC')
           // ->setMaxResults(10)
            //->getQuery()
           // ->getResult()
        ;
        return $qb->getQuery()->getResult();
    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
