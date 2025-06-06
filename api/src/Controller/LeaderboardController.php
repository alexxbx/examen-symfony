<?php

namespace App\Controller;

use App\Entity\Leaderboard;
use App\Repository\LeaderboardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api/leaderboard')]
class LeaderboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LeaderboardRepository $leaderboardRepository,
        private UserRepository $userRepository,
        private Security $security
    ) {}

    #[Route('/top', name: 'leaderboard_top', methods: ['GET'])]
    public function getTopPlayers(): JsonResponse
    {
        $topPlayers = $this->leaderboardRepository->findTopPlayers();
        
        $players = array_map(function (Leaderboard $entry) {
            return [
                'rank' => $this->leaderboardRepository->findPlayerRank($entry->getUser()->getId()),
                'user' => [
                    'id' => $entry->getUser()->getId(),
                    'username' => $entry->getUser()->getUsername()
                ],
                'totalGames' => $entry->getTotalGames(),
                'wins' => $entry->getWins(),
                'losses' => $entry->getLosses(),
                'draws' => $entry->getDraws(),
                'totalPoints' => $entry->getTotalPoints()
            ];
        }, $topPlayers);

        return new JsonResponse($players);
    }

    #[Route('/user/{userId}', name: 'leaderboard_user', methods: ['GET'])]
    public function getUserStats(int $userId): JsonResponse
    {
        $stats = $this->leaderboardRepository->findPlayerStats($userId);
        
        if (!$stats) {
            return new JsonResponse(['message' => 'Statistiques non trouvées'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'rank' => $this->leaderboardRepository->findPlayerRank($userId),
            'user' => [
                'id' => $stats->getUser()->getId(),
                'username' => $stats->getUser()->getUsername()
            ],
            'totalGames' => $stats->getTotalGames(),
            'wins' => $stats->getWins(),
            'losses' => $stats->getLosses(),
            'draws' => $stats->getDraws(),
            'totalPoints' => $stats->getTotalPoints(),
            'lastUpdated' => $stats->getLastUpdated()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/update/{gameId}', name: 'leaderboard_update', methods: ['POST'])]
    public function updateLeaderboard(int $gameId): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer la partie
        $game = $this->entityManager->getRepository('App\Entity\GameSession')->find($gameId);
        if (!$game) {
            return new JsonResponse(['message' => 'Partie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les statistiques pour l'hôte
        $hostStats = $this->leaderboardRepository->findPlayerStats($game->getHost()->getId());
        if (!$hostStats) {
            $hostStats = new Leaderboard();
            $hostStats->setUser($game->getHost());
        }

        $hostResult = $game->getWinner() === $game->getHost() ? 'WIN' : 
                     ($game->getWinner() === $game->getGuest() ? 'LOSS' : 'DRAW');
        $hostStats->updateStats($hostResult);
        $this->entityManager->persist($hostStats);

        // Mettre à jour les statistiques pour l'invité
        if ($game->getGuest()) {
            $guestStats = $this->leaderboardRepository->findPlayerStats($game->getGuest()->getId());
            if (!$guestStats) {
                $guestStats = new Leaderboard();
                $guestStats->setUser($game->getGuest());
            }

            $guestResult = $game->getWinner() === $game->getGuest() ? 'WIN' : 
                          ($game->getWinner() === $game->getHost() ? 'LOSS' : 'DRAW');
            $guestStats->updateStats($guestResult);
            $this->entityManager->persist($guestStats);
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Classement mis à jour avec succès']);
    }
} 