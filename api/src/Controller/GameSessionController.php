<?php

namespace App\Controller;

use App\Entity\GameSession;
use App\Repository\GameSessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api/game-sessions')]
class GameSessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GameSessionRepository $gameSessionRepository,
        private UserRepository $userRepository,
        private Security $security
    ) {}

    #[Route('', name: 'game_session_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $gameSession = new GameSession();
        $gameSession->setHost($user);
        $gameSession->setStatus('WAITING');

        $this->entityManager->persist($gameSession);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $gameSession->getId(),
            'status' => $gameSession->getStatus(),
            'host' => [
                $user = $this->security->getUser(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}/join', name: 'game_session_join', methods: ['POST'])]
    public function join(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $gameSession = $this->gameSessionRepository->find($id);
        if (!$gameSession) {
            return new JsonResponse(['message' => 'Session de jeu non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if ($gameSession->getStatus() !== 'WAITING') {
            return new JsonResponse(['message' => 'Cette session n\'est plus disponible'], Response::HTTP_BAD_REQUEST);
        }


        $gameSession->setGuest($user);
        $gameSession->setStatus('IN_PROGRESS');
        $gameSession->setStartedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $gameSession->getId(),
            'status' => $gameSession->getStatus(),
            'host' => [
                'id' => $gameSession->getHost()->getId(),
                'username' => $gameSession->getHost()->getUsername()
            ],
            'guest' => [
                /** @var \App\Entity\User $user */
                $user = $this->security->getUser(),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}/update-score', name: 'game_session_update_score', methods: ['POST'])]
    public function updateScore(int $id, Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $gameSession = $this->gameSessionRepository->find($id);
        if (!$gameSession) {
            return new JsonResponse(['message' => 'Session de jeu non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if ($gameSession->getStatus() !== 'IN_PROGRESS') {
            return new JsonResponse(['message' => 'Cette session n\'est pas en cours'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['hostScore']) || !isset($data['guestScore'])) {
            return new JsonResponse(['message' => 'Scores manquants'], Response::HTTP_BAD_REQUEST);
        }

        $gameSession->setHostScore($data['hostScore']);
        $gameSession->setGuestScore($data['guestScore']);

        // Déterminer le gagnant
        if ($data['hostScore'] > $data['guestScore']) {
            $gameSession->setWinner($gameSession->getHost());
        } elseif ($data['guestScore'] > $data['hostScore']) {
            $gameSession->setWinner($gameSession->getGuest());
        }

        $gameSession->setStatus('COMPLETED');
        $gameSession->setEndedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $gameSession->getId(),
            'status' => $gameSession->getStatus(),
            'hostScore' => $gameSession->getHostScore(),
            'guestScore' => $gameSession->getGuestScore(),
            'winner' => $gameSession->getWinner() ? [
                'id' => $gameSession->getWinner()->getId(),
                'username' => $gameSession->getWinner()->getUsername()
            ] : null
        ]);
    }

    #[Route('/waiting', name: 'game_session_waiting', methods: ['GET'])]
    public function getWaitingGames(): JsonResponse
    {
        $waitingGames = $this->gameSessionRepository->findWaitingGames();
        
        $games = array_map(function (GameSession $game) {
            return [
                'id' => $game->getId(),
                'host' => [
                    'id' => $game->getHost()->getId(),
                    'username' => $game->getHost()->getUsername()
                ],
                'createdAt' => $game->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $waitingGames);

        return new JsonResponse($games);
    }

    #[Route('/user/{userId}', name: 'game_session_user', methods: ['GET'])]
    public function getUserGames(int $userId): JsonResponse
    {
        $games = $this->gameSessionRepository->findUserGames($userId);
        
        $gameData = array_map(function (GameSession $game) {
            return [
                'id' => $game->getId(),
                'status' => $game->getStatus(),
                'host' => [
                    'id' => $game->getHost()->getId(),
                    'username' => $game->getHost()->getUsername()
                ],
                'guest' => $game->getGuest() ? [
                    'id' => $game->getGuest()->getId(),
                    'username' => $game->getGuest()->getUsername()
                ] : null,
                'hostScore' => $game->getHostScore(),
                'guestScore' => $game->getGuestScore(),
                'winner' => $game->getWinner() ? [
                    'id' => $game->getWinner()->getId(),
                    'username' => $game->getWinner()->getUsername()
                ] : null,
                'createdAt' => $game->getCreatedAt()->format('Y-m-d H:i:s'),
                'startedAt' => $game->getStartedAt() ? $game->getStartedAt()->format('Y-m-d H:i:s') : null,
                'endedAt' => $game->getEndedAt() ? $game->getEndedAt()->format('Y-m-d H:i:s') : null
            ];
        }, $games);

        return new JsonResponse($gameData);
    }
} 