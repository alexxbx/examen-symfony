<?php

namespace App\Service;

use App\Entity\Achievement;
use App\Entity\User;
use App\Event\AchievementUnlockedEvent;
use App\Repository\AchievementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AchievementService
{
    private $entityManager;
    private $achievementRepository;
    private $userRepository;
    private $eventDispatcher;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        AchievementRepository $achievementRepository,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->achievementRepository = $achievementRepository;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    public function checkAndUnlockAchievements(int $userId): void
    {
        $this->logger->info('Vérification des succès pour l\'utilisateur', ['userId' => $userId]);

        $user = $this->userRepository->find($userId);
        if (!$user) {
            $this->logger->error('Utilisateur non trouvé', ['userId' => $userId]);
            return;
        }

        // Compter le nombre de leçons complétées
        $completedLessons = $this->countCompletedLessons($user);
        $this->logger->info('Nombre de leçons complétées', ['completedLessons' => $completedLessons]);

        // Récupérer les succès qui peuvent être débloqués
        $achievements = $this->achievementRepository->findAchievementsByRequiredLessons($completedLessons);
        $this->logger->info('Succès trouvés', ['count' => count($achievements)]);

        foreach ($achievements as $baseAchievement) {
            // Vérifier si l'utilisateur a déjà ce succès
            $existingAchievement = $this->achievementRepository->findOneBy([
                'user' => $user,
                'title' => $baseAchievement->getTitle()
            ]);

            if ($existingAchievement) {
                $this->logger->info('Succès déjà débloqué', [
                    'userId' => $userId,
                    'achievementTitle' => $baseAchievement->getTitle()
                ]);
                continue;
            }

            // Créer une nouvelle instance du succès pour l'utilisateur
            $achievement = new Achievement();
            $achievement->setUser($user);
            $achievement->setTitle($baseAchievement->getTitle());
            $achievement->setDescription($baseAchievement->getDescription());
            $achievement->setRequiredLessons($baseAchievement->getRequiredLessons());
            $achievement->setIcon($baseAchievement->getIcon());
            $achievement->setUnlockedAt(new \DateTime());

            $this->entityManager->persist($achievement);
            $this->entityManager->flush();

            $this->logger->info('Succès débloqué', [
                'userId' => $userId,
                'achievementTitle' => $achievement->getTitle()
            ]);

            // Déclencher l'événement de déblocage
            $this->eventDispatcher->dispatch(
                new AchievementUnlockedEvent($user, $achievement),
                AchievementUnlockedEvent::NAME
            );
        }
    }

    private function countCompletedLessons(User $user): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        return $qb->select('COUNT(p.id)')
            ->from('App\Entity\Progression', 'p')
            ->where('p.user = :user')
            ->andWhere('p.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getUserAchievements(User $user): array
    {
        return [
            'unlocked' => $this->achievementRepository->findUnlockedAchievementsByUser($user->getId()),
            'locked' => $this->achievementRepository->findLockedAchievementsByUser($user->getId())
        ];
    }
} 