<?php
namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findCommandesNonLues(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.messages', 'msg')
            ->where('msg.isAdmin = false')
            ->andWhere('msg.lu = false')
            ->orderBy('msg.dateEnvoi', 'DESC')
            ->getQuery()
            ->getResult();
    }
}