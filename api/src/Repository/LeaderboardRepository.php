<?php

namespace App\Repository;

use App\Entity\Leaderboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LeaderboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leaderboard::class);
    }

    public function findTopPlayers(int $limit = 10): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.totalPoints', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPlayerRank(int $userId): ?int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l2.id) + 1')
            ->leftJoin('l2', Leaderboard::class, 'l2', 'l2.totalPoints > l.totalPoints')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findPlayerStats(int $userId): ?Leaderboard
    {
        return $this->findOneBy(['user' => $userId]);
    }
} 