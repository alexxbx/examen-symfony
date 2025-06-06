<?php
// src/Controller/ProgressionController.php

namespace App\Controller;

use App\Entity\Progression;
use App\Entity\User;
use App\Entity\Lesson;
use App\Service\ProgressionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgressionController extends AbstractController
{
    private $progressionService;
    private $entityManager;

    public function __construct(
        ProgressionService $progressionService,
        EntityManagerInterface $entityManager
    ) {
        $this->progressionService = $progressionService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/progressions', name: 'create_progression', methods: ['POST'])]
    public function createProgression(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['user']) || !isset($data['lesson'])) {
                return $this->json([
                    'error' => 'user et lesson sont requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Extraire les IDs des URLs
            $userId = basename($data['user']);
            $lessonId = basename($data['lesson']);

            $user = $this->entityManager->getRepository(User::class)->find($userId);
            $lesson = $this->entityManager->getRepository(Lesson::class)->find($lessonId);

            if (!$user || !$lesson) {
                return $this->json([
                    'error' => 'Utilisateur ou leçon non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $progression = $this->progressionService->createOrUpdateProgression(
                $user,
                $lesson,
                $data['completed'] ?? false
            );

            return $this->json([
                'message' => 'Progression enregistrée avec succès',
                'progression' => [
                    'id' => $progression->getId(),
                    'userId' => $progression->getUser()->getId(),
                    'lessonId' => $progression->getLesson()->getId(),
                    'completed' => $progression->isCompleted(),
                    'unlocked' => $progression->isUnlocked()
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'enregistrement de la progression: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/progressions/{id}', name: 'update_progression', methods: ['PUT'])]
    public function updateProgression(int $id, Request $request): JsonResponse
    {
        try {
            $progression = $this->entityManager->getRepository(Progression::class)->find($id);
            if (!$progression) {
                return $this->json(['error' => 'Progression non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            // Mettre à jour les champs
            if (isset($data['completed'])) {
                $progression->setCompleted((bool)$data['completed']);
            }
            if (isset($data['unlocked'])) {
                $progression->setUnlocked((bool)$data['unlocked']);
            }

            // Forcer la mise à jour
            $this->entityManager->persist($progression);
            $this->entityManager->flush();

            // Vérifier que la mise à jour a bien été effectuée
            $this->entityManager->refresh($progression);

            // Retourner la progression mise à jour
            return $this->json([
                'message' => 'Progression mise à jour avec succès',
                'progression' => [
                    'id' => $progression->getId(),
                    'userId' => $progression->getUser()->getId(),
                    'lessonId' => $progression->getLesson()->getId(),
                    'completed' => $progression->isCompleted(),
                    'unlocked' => $progression->isUnlocked()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour de la progression: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/progressions/{userId}', name: 'get_user_progressions', methods: ['GET'])]
    public function getUserProgressions(int $userId): JsonResponse
    {
        // Récupérer les progressions de l'utilisateur, y compris les leçons
        $progressions = $this->progressionService->getUserProgressions($userId);

        // Retourner les progressions au format JSON
        return $this->json($progressions);
    }
}
