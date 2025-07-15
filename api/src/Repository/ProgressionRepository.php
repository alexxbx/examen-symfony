<?php

// src/Repository/ProgressionRepository.php

namespace App\Repository;

use App\Entity\Progression;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Progression>
 */
class ProgressionRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Progression::class);
        $this->entityManager = $entityManager;
    }

    public function findWithLessonsByUser(int $userId): array
    {
        // Récupérer toutes les progressions avec les leçons associées
        return $this->createQueryBuilder('p')
            ->join('p.lesson', 'l')          // Jointure avec la table Lesson
            ->addSelect('l')                 // Sélectionner les données de la leçon
            ->where('p.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findProgressionForUserAndLesson(int $userId, int $lessonId): ?Progression
    {
        return $this->findOneBy([
            'user' => $userId,
            'lesson' => $lessonId
        ]);
    }

    public function findLastCompletedLesson(int $userId): ?Progression
    {
        return $this->createQueryBuilder('p')
            ->join('p.lesson', 'l')
            ->where('p.user = :userId')
            ->andWhere('p.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('completed', true)
            ->orderBy('l.order', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextUnlockedLesson(int $userId): ?Progression
    {
        return $this->createQueryBuilder('p')
            ->join('p.lesson', 'l')
            ->where('p.user = :userId')
            ->andWhere('p.unlocked = :unlocked')
            ->andWhere('p.completed = :completed')
            ->setParameter('userId', $userId)
            ->setParameter('unlocked', true)
            ->setParameter('completed', false)
            ->orderBy('l.order', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUnlockedLessonsByUser(int $userId): array
{
    return $this->createQueryBuilder('p')
        ->join('p.lesson', 'l')
        ->addSelect('l')
        ->where('p.user = :userId')
        ->andWhere('p.unlocked = true')
        ->setParameter('userId', $userId)
        ->getQuery()
        ->getResult();
}

    public function getLessonsLeaderboard(): array
    {
        return $this->createQueryBuilder('p')
            ->select('u.id as userId, u.username, COUNT(p.id) as lessonsCompleted')
            ->join('p.user', 'u')
            ->where('p.completed = true')
            ->groupBy('u.id')
            ->orderBy('lessonsCompleted', 'DESC')
            ->getQuery()
            ->getResult();
    }


    public function save(Progression $progression): void
    {
        $this->entityManager->persist($progression);
        $this->entityManager->flush(); // Obligatoire pour enregistrer en base
    }
}
