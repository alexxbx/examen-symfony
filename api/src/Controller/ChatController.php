<?php

namespace App\Controller;

use App\Entity\ChatMessage;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChatMessageRepository $chatMessageRepository
    ) {
    }

    #[Route('/api/chat/{userId}/messages', name: 'get_chat_messages', methods: ['GET'])]
    public function getMessages(int $userId): JsonResponse
    {
        $messages = $this->chatMessageRepository->findByUser($userId);
        
        $data = array_map(function (ChatMessage $message) {
            return [
                'id' => $message->getId(),
                'message' => $message->getMessage(),
                'isFromUser' => $message->isFromUser(),
                'createdAt' => $message->getCreatedAt()->format(\DateTimeInterface::ATOM)
            ];
        }, $messages);

        return $this->json($data);
    }

    #[Route('/api/chat/{userId}/messages', name: 'create_chat_message', methods: ['POST'])]
    public function createMessage(int $userId, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['message'])) {
                return $this->json(['error' => 'Le message est requis'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$user) {
                return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
            }

            // Créer le message de l'utilisateur
            $userMessage = new ChatMessage();
            $userMessage->setUser($user);
            $userMessage->setMessage($data['message']);
            $userMessage->setIsFromUser(true);
            $this->entityManager->persist($userMessage);

            // Simuler une réponse du chatbot (à remplacer par l'intégration avec une vraie IA)
            $botMessage = new ChatMessage();
            $botMessage->setUser($user);
            $botMessage->setMessage("Je suis un chatbot en cours de développement. Je ne peux pas encore répondre à vos messages.");
            $botMessage->setIsFromUser(false);
            $this->entityManager->persist($botMessage);

            $this->entityManager->flush();

            return $this->json([
                'userMessage' => [
                    'id' => $userMessage->getId(),
                    'message' => $userMessage->getMessage(),
                    'isFromUser' => $userMessage->isFromUser(),
                    'createdAt' => $userMessage->getCreatedAt()->format(\DateTimeInterface::ATOM)
                ],
                'botMessage' => [
                    'id' => $botMessage->getId(),
                    'message' => $botMessage->getMessage(),
                    'isFromUser' => $botMessage->isFromUser(),
                    'createdAt' => $botMessage->getCreatedAt()->format(\DateTimeInterface::ATOM)
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la création du message'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/chat/{userId}/messages/{messageId}', name: 'delete_chat_message', methods: ['DELETE'])]
    public function deleteMessage(int $userId, int $messageId): JsonResponse
    {
        $message = $this->chatMessageRepository->find($messageId);
        
        if (!$message) {
            return $this->json(['error' => 'Message non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($message->getUser()->getId() !== $userId) {
            return $this->json(['error' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($message);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
} 