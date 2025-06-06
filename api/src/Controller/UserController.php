<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }
    public function index(UserRepository $repository): JsonResponse
    {
        $users = $repository->findAll();
        
        $data = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'roles' => $user->getRoles(),
            ];
        }, $users);
        
        return $this->json($data);
    }

    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
    
        if (!$user) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
    
        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'roles' => $user->getRoles(),
        ]);
    }

    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
    
        $user = new User();
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setCreatedAt(new \DateTimeImmutable());
        $roles = $data['roles'] ?? ['ROLE_USER'];
        if (is_string($roles)) {
        $roles = array_map('trim', explode(',', $roles));
        }
        if (empty($roles)) {
            $roles = ['ROLE_USER'];
        }
        $user->setRoles($roles);
        
        try {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password'] ?? ''));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Password hashing failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
    
        $em->persist($user);
        $em->flush();
    
        return $this->json(
            ['message' => 'User created successfully'],
            Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('user_show', ['id' => $user->getId()])]
        );
    }

    public function update(Request $request, User $user, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }
        
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }
        if (isset($data['roles'])) {
            $user->setRoles((array)$data['roles']);
        }
        
        $errors = $this->validator->validate($user);
        try {
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Validation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $em->flush();
        
        return $this->json(['message' => 'User updated successfully']);
    }

    public function delete(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    public function logout(): JsonResponse
    {
        // Aucune action backend avec JWT stateless
        return new JsonResponse(['message' => 'Logout successful'], 200);
    }
}