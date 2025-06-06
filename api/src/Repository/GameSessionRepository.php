<?php

namespace App\Repository;

use App\Entity\GameSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GameSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameSession::class);
    }

    public function findActiveGames(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'IN_PROGRESS')
            ->getQuery()
            ->getResult();
    }

    public function findWaitingGames(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'WAITING')
            ->getQuery()
            ->getResult();
    }

    public function findUserGames(int $userId): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.host = :userId OR g.guest = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 