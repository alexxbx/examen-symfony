<?php

namespace App\Controller;

use App\Service\AchievementService;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class AchievementController extends AbstractController
{
    private $achievementService;
    private $entityManager;
    private $logger;

    public function __construct(
        AchievementService $achievementService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->achievementService = $achievementService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    #[Route('/api/user/{userId}/achievements', name: 'get_user_achievements', methods: ['GET'], requirements: ['userId' => '\\d+'])]
    public function getUserAchievements($userId): JsonResponse
    {
        try {
            $this->logger->info('Tentative de récupération des succès pour l\'utilisateur', ['userId' => $userId]);
            
            $userId = (int) $userId;
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            
            if (!$user) {
                $this->logger->warning('Utilisateur non trouvé', ['userId' => $userId]);
                return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $achievements = $this->achievementService->getUserAchievements($user);
            $this->logger->info('Succès récupérés avec succès', [
                'userId' => $userId,
                'unlockedCount' => count($achievements['unlocked']),
                'lockedCount' => count($achievements['locked'])
            ]);

            $response = [
                'unlocked' => array_map(function($achievement) {
                    return [
                        'id' => $achievement->getId(),
                        'title' => $achievement->getTitle(),
                        'description' => $achievement->getDescription(),
                        'icon' => $achievement->getIcon(),
                        'unlockedAt' => $achievement->getUnlockedAt() ? $achievement->getUnlockedAt()->format('Y-m-d H:i:s') : null
                    ];
                }, $achievements['unlocked']),
                'locked' => array_map(function($achievement) {
                    return [
                        'id' => $achievement->getId(),
                        'title' => $achievement->getTitle(),
                        'description' => $achievement->getDescription(),
                        'icon' => $achievement->getIcon(),
                        'requiredLessons' => $achievement->getRequiredLessons()
                    ];
                }, $achievements['locked'])
            ];

            $this->logger->info('Réponse formatée', ['response' => $response]);
            
            return $this->json($response);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des succès', [
                'userId' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 