<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    // Commandes avec messages non lus, triées par dernier message
  /*  public function findCommandesNonLues(): array
    {
        return $this->createQueryBuilder('msg')
            ->select('DISTINCT c')
            ->from(\App\Entity\Commande::class, 'c')
            ->join('c.messages', 'msg')
            ->where('msg.isAdmin = false')
            ->andWhere('msg.lu = false')
            ->orderBy('msg.dateEnvoi', 'DESC')
            ->getQuery()
            ->getResult();
    }*/
}