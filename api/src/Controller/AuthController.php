<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $identifier = $data['username'] ?? $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$identifier || !$password) {
            return new JsonResponse(['error' => 'Missing credentials'], 400);
        }

        $user = $userRepository->findOneBy(['username' => $identifier])
            ?? $userRepository->findOneBy(['email' => $identifier]);

        if (
            !$user
            || !$user instanceof UserInterface
            || !$user instanceof PasswordAuthenticatedUserInterface
            || !$passwordHasher->isPasswordValid($user, $password)
        ) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
