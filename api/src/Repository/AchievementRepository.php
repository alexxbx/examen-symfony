<?php

namespace App\Repository;

use App\Entity\Achievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achievement::class);
    }

    public function findUnlockedAchievementsByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :userId')
            ->andWhere('a.unlockedAt IS NOT NULL')
            ->setParameter('userId', $userId)
            ->orderBy('a.unlockedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLockedAchievementsByUser(int $userId): array
    {
        // Récupérer tous les succès qui ne sont pas débloqués par l'utilisateur
        $qb = $this->createQueryBuilder('a');
        return $qb->where($qb->expr()->orX(
            $qb->expr()->isNull('a.user'),
            $qb->expr()->andX(
                $qb->expr()->eq('a.user', ':userId'),
                $qb->expr()->isNull('a.unlockedAt')
            )
        ))
        ->setParameter('userId', $userId)
        ->orderBy('a.requiredLessons', 'ASC')
        ->getQuery()
        ->getResult();
    }

    public function findAchievementsByRequiredLessons(int $completedLessons): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.requiredLessons <= :completedLessons')
            ->andWhere('a.user IS NULL') // Ne prendre que les succès de base
            ->setParameter('completedLessons', $completedLessons)
            ->orderBy('a.requiredLessons', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBaseAchievementByTitle(string $title): ?Achievement
    {
        return $this->createQueryBuilder('a')
            ->where('a.title = :title')
            ->andWhere('a.user IS NULL')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 