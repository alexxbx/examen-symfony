<?php

// src/Service/ProgressionService.php

namespace App\Service;

use App\Repository\ProgressionRepository;
use App\Repository\LessonRepository;
use App\Entity\Progression;
use App\Entity\User;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\LessonCompletedEvent;

class ProgressionService
{
    private $progressionRepository;
    private $lessonRepository;
    private $em;
    private $achievementService;
    private $eventDispatcher;

    public function __construct(
        ProgressionRepository $progressionRepository,
        LessonRepository $lessonRepository,
        EntityManagerInterface $em,
        AchievementService $achievementService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->progressionRepository = $progressionRepository;
        $this->lessonRepository = $lessonRepository;
        $this->em = $em;
        $this->achievementService = $achievementService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createOrUpdateProgression(User $user, Lesson $lesson, bool $completed): Progression
    {
        $progression = $this->progressionRepository->findProgressionForUserAndLesson(
            $user->getId(),
            $lesson->getId()
        );

        if (!$progression) {
            $progression = new Progression();
            $progression->setUser($user);
            $progression->setLesson($lesson);
            $progression->setUnlocked(true);
            $progression->setCompleted(false);
        }

        // Toujours mettre à jour le statut completed
        $progression->setCompleted($completed);
        
        // Si la leçon est complétée, s'assurer qu'elle est débloquée
        if ($completed) {
            $progression->setUnlocked(true);
        }

        $this->em->persist($progression);
        $this->em->flush();

        // Si la leçon est complétée, débloquer la suivante et déclencher l'événement
        if ($completed) {
            $this->unlockNextLesson($user, $lesson);
            
            // Déclencher l'événement de complétion de leçon
            $this->eventDispatcher->dispatch(
                new LessonCompletedEvent($user, $lesson),
                LessonCompletedEvent::NAME
            );
        }

        return $progression;
    }

    public function unlockNextLesson(User $user, Lesson $currentLesson): void
    {
        // D'abord, marquer la leçon actuelle comme complétée
        $currentProgression = $this->progressionRepository->findOneBy([
            'user' => $user,
            'lesson' => $currentLesson
        ]);

        if ($currentProgression) {
            $currentProgression->setCompleted(true);
            $currentProgression->setUnlocked(true);
            $this->em->persist($currentProgression);
        }

        // Ensuite, débloquer la prochaine leçon
        $nextLesson = $this->lessonRepository->createQueryBuilder('l')
            ->where('l.order > :currentOrder')
            ->setParameter('currentOrder', $currentLesson->getOrder())
            ->orderBy('l.order', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    
        if ($nextLesson) {
            // Vérifie si une progression existe déjà
            $existingProgression = $this->progressionRepository->findOneBy([
                'user' => $user,
                'lesson' => $nextLesson
            ]);
    
            if (!$existingProgression) {
                $progression = new Progression();
                $progression->setUser($user);
                $progression->setLesson($nextLesson);
                $progression->setCompleted(false);
                $progression->setUnlocked(true);
                $this->em->persist($progression);
            } else {
                $existingProgression->setUnlocked(true);
                $this->em->persist($existingProgression);
            }
        }

        // Sauvegarder toutes les modifications
        $this->em->flush();
    }

    // Récupérer les progressions avec leurs leçons associées
    public function getUserProgressions(int $userId): array
    {
        $progressions = $this->progressionRepository->findBy(['user' => $userId]);
        
        return array_map(function (Progression $progression) {
            return [
                'id' => $progression->getId(),
                'userId' => $progression->getUser()->getId(),
                'lessonId' => $progression->getLesson()->getId(),
                'completed' => $progression->isCompleted(),
                'unlocked' => $progression->isUnlocked()
            ];
        }, $progressions);
    }
}
