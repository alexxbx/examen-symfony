<?php

namespace App\Controller;

use App\Repository\LessonRepository;
use App\Repository\ProgressionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Service\LessonUnlockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Lesson;
use App\Entity\Progression;

class UserLessonController extends AbstractController
{
    private LessonRepository $lessonRepository;
    private ProgressionRepository $progressionRepository;
    private LessonUnlockService $lessonUnlockService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        LessonRepository $lessonRepository,
        ProgressionRepository $progressionRepository,
        LessonUnlockService $lessonUnlockService,
        EntityManagerInterface $entityManager
    ) {
        $this->lessonRepository = $lessonRepository;
        $this->progressionRepository = $progressionRepository;
        $this->lessonUnlockService = $lessonUnlockService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/user/{userId}/lessons-unlocked', name: 'get_lessons_unlocked', methods: ['GET'])]
    public function getLessonsWithUnlockStatus(int $userId): JsonResponse
    {
        try {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            }

            // Nettoyer les doublons
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('p', 'l')
               ->from('App\Entity\Progression', 'p')
               ->join('p.lesson', 'l')
               ->where('p.user = :userId')
               ->setParameter('userId', $userId);
            
            $progressions = $qb->getQuery()->getResult();
            
            // Grouper les progressions par leçon
            $progressionsByLesson = [];
            foreach ($progressions as $progression) {
                $lessonId = $progression->getLesson()->getId();
                if (!isset($progressionsByLesson[$lessonId])) {
                    $progressionsByLesson[$lessonId] = [];
                }
                $progressionsByLesson[$lessonId][] = $progression;
            }

            // Nettoyer les doublons et mettre à jour les statuts
            foreach ($progressionsByLesson as $lessonId => $lessonProgressions) {
                if (count($lessonProgressions) > 1) {
                    // Garder la progression la plus récente
                    usort($lessonProgressions, function($a, $b) {
                        return $b->getUpdatedAt() <=> $a->getUpdatedAt();
                    });
                    
                    // Si la progression la plus récente est complétée, s'assurer qu'elle est débloquée
                    if ($lessonProgressions[0]->isCompleted()) {
                        $lessonProgressions[0]->setUnlocked(true);
                        $this->entityManager->persist($lessonProgressions[0]);
                    }
                    
                    // Supprimer les anciennes progressions
                    for ($i = 1; $i < count($lessonProgressions); $i++) {
                        $this->entityManager->remove($lessonProgressions[$i]);
                    }
                } else {
                    // Pour les leçons avec une seule progression, s'assurer que completed = true implique unlocked = true
                    if ($lessonProgressions[0]->isCompleted()) {
                        $lessonProgressions[0]->setUnlocked(true);
                        $this->entityManager->persist($lessonProgressions[0]);
                    }
                }
            }
            $this->entityManager->flush();

            // Récupérer les leçons avec leur statut mis à jour
            $lessons = $this->entityManager->getRepository(Lesson::class)->findBy([], ['order' => 'ASC']);
            $result = [];

            foreach ($lessons as $lesson) {
                $progression = $this->entityManager->getRepository(Progression::class)
                    ->findOneBy(['user' => $user, 'lesson' => $lesson]);

                $isUnlocked = false;
                $completed = false;

                if ($progression) {
                    $completed = $progression->isCompleted();
                    // Si la leçon est complétée, elle est forcément débloquée
                    $isUnlocked = $completed || $progression->isUnlocked();
                    
                    // Mettre à jour la progression si nécessaire
                    if ($completed && !$progression->isUnlocked()) {
                        $progression->setUnlocked(true);
                        $this->entityManager->persist($progression);
                    }
                } else if ($lesson->getOrder() === 1) {
                    // La première leçon est toujours débloquée
                    $isUnlocked = true;
                    $progression = new Progression();
                    $progression->setUser($user);
                    $progression->setLesson($lesson);
                    $progression->setUnlocked(true);
                    $progression->setCompleted(false);
                    $this->entityManager->persist($progression);
                } else {
                    // Vérifier si la leçon précédente est complétée
                    $previousLesson = $this->entityManager->getRepository(Lesson::class)
                        ->findOneBy(['order' => $lesson->getOrder() - 1]);
                    
                    if ($previousLesson) {
                        $previousProgression = $this->entityManager->getRepository(Progression::class)
                            ->findOneBy(['user' => $user, 'lesson' => $previousLesson]);
                        
                        if ($previousProgression && $previousProgression->isCompleted()) {
                            $isUnlocked = true;
                            $progression = new Progression();
                            $progression->setUser($user);
                            $progression->setLesson($lesson);
                            $progression->setUnlocked(true);
                            $progression->setCompleted(false);
                            $this->entityManager->persist($progression);
                        }
                    }
                }

                $this->entityManager->flush();

                $result[] = [
                    'id' => $lesson->getId(),
                    'title' => $lesson->getTitle(),
                    'content' => $lesson->getContent(),
                    'order' => $lesson->getOrder(),
                    'isUnlocked' => $isUnlocked,
                    'completed' => $completed
                ];
            }

            return $this->json($result);
        } catch (\Exception $e) {
            error_log("Erreur dans getLessonsWithUnlockStatus: " . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/user/{id}/unlock-next-lesson', name: 'unlock_next_lesson', methods: ['POST'])]
    public function unlockNextLesson(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->lessonUnlockService->unlockNextLesson($user);
            return new JsonResponse(['message' => 'Prochaine leçon débloquée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}