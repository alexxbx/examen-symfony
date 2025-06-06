<?php
// src/Service/LessonUnlockService.php

namespace App\Service;

use App\Repository\ProgressionRepository;
use App\Repository\LessonRepository;
use App\Entity\User;
use App\Entity\Progression;

class LessonUnlockService
{
    private ProgressionRepository $progressionRepository;
    private LessonRepository $lessonRepository;

    public function __construct(ProgressionRepository $progressionRepository, LessonRepository $lessonRepository)
    {
        $this->progressionRepository = $progressionRepository;
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * Débloquer la leçon suivante après que l'utilisateur termine la leçon actuelle.
     */
    public function unlockNextLesson(User $user): void
    {
        // Trouver la dernière leçon terminée
        $lastCompletedProgression = $this->progressionRepository->findLastCompletedLesson($user->getId());
        
        // Si aucune leçon n'est terminée, débloquer la première leçon
        if (!$lastCompletedProgression) {
            $firstLesson = $this->lessonRepository->findBy([], ['order' => 'ASC'], 1)[0] ?? null;
            if ($firstLesson) {
                $progression = $this->progressionRepository->findProgressionForUserAndLesson($user->getId(), $firstLesson->getId());
                if (!$progression) {
                    $progression = new Progression();
                    $progression->setUser($user);
                    $progression->setLesson($firstLesson);
                }
                $progression->setUnlocked(true);
                $progression->setCompleted(false);
                $this->progressionRepository->save($progression);
            }
            return;
        }

        // Trouver la prochaine leçon dans l'ordre
        $nextLesson = $this->lessonRepository->findNextLesson($lastCompletedProgression->getLesson());

        if ($nextLesson) {
            // Vérifier si la leçon suivante est déjà déverrouillée
            $nextProgression = $this->progressionRepository->findProgressionForUserAndLesson($user->getId(), $nextLesson->getId());
            
            if (!$nextProgression) {
                $nextProgression = new Progression();
                $nextProgression->setUser($user);
                $nextProgression->setLesson($nextLesson);
            }
            
            // Débloquer la prochaine leçon
            $nextProgression->setUnlocked(true);
            $nextProgression->setCompleted(false);
            $this->progressionRepository->save($nextProgression);

            // Forcer la mise à jour en base de données
            $this->progressionRepository->getEntityManager()->flush();

            // Débloquer également toutes les leçons précédentes
            $previousLessons = $this->lessonRepository->createQueryBuilder('l')
                ->where('l.order <= :currentOrder')
                ->setParameter('currentOrder', $nextLesson->getOrder())
                ->getQuery()
                ->getResult();

            foreach ($previousLessons as $previousLesson) {
                $previousProgression = $this->progressionRepository->findProgressionForUserAndLesson($user->getId(), $previousLesson->getId());
                if (!$previousProgression) {
                    $previousProgression = new Progression();
                    $previousProgression->setUser($user);
                    $previousProgression->setLesson($previousLesson);
                }
                $previousProgression->setUnlocked(true);
                $this->progressionRepository->save($previousProgression);
            }
            $this->progressionRepository->getEntityManager()->flush();
        }
    }
}
