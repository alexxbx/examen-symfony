<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse(['message' => 'API is working!']);
    }

    #[Route('/api/test-users', name: 'api_test_users', methods: ['GET'])]
    public function testUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $userData = [];
        
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ];
        }
        
        return new JsonResponse([
            'count' => count($users),
            'users' => $userData
        ]);
    }

    #[Route('/api/test-jwt', name: 'api_test_jwt', methods: ['GET'])]
    public function testJwt(): JsonResponse
    {
        $privateKeyPath = '/var/www/api/config/jwt/private.pem';
        $publicKeyPath = '/var/www/api/config/jwt/public.pem';
        
        return new JsonResponse([
            'private_key_exists' => file_exists($privateKeyPath),
            'public_key_exists' => file_exists($publicKeyPath),
            'private_key_readable' => is_readable($privateKeyPath),
            'public_key_readable' => is_readable($publicKeyPath),
            'jwt_secret_key_env' => $_ENV['JWT_SECRET_KEY'] ?? 'not_set',
            'jwt_public_key_env' => $_ENV['JWT_PUBLIC_KEY'] ?? 'not_set',
            'jwt_passphrase_env' => $_ENV['JWT_PASSPHRASE'] ?? 'not_set'
        ]);
    }
}
