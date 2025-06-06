<?php
// src/Controller/ExerciseController.php
namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Exercise;
use App\Service\ErrorNormalizer;
use App\Service\ExerciseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/exercises')]
class ExerciseController extends AbstractApiController
{
    private ExerciseService $exerciseService;
    private ValidatorInterface $validator;
    
    public function __construct(
        ErrorNormalizer $errorNormalizer,
        ExerciseService $exerciseService,
        ValidatorInterface $validator
    ) {
        parent::__construct($errorNormalizer);
        $this->exerciseService = $exerciseService;
        $this->validator = $validator;
    }
    
    #[Route('', name: 'exercise_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): JsonResponse
    {
        $exercises = $this->exerciseService->getAllExercises();
        return $this->json($exercises);
    }
    
    #[Route('/{id}', name: 'exercise_show', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(int $id): JsonResponse
    {
        $exercise = $this->exerciseService->getExerciseById($id);
        
        if (!$exercise) {
            return $this->createNotFoundResponse('Exercice');
        }
        
        return $this->json($exercise);
    }

    #[Route('', name: 'exercise_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->decodeJsonRequest($request);
            
            $exercise = $this->exerciseService->createExercise(
                $data['question'] ?? '',
                $data['type'] ?? '',
                $data['options'] ?? [],
                $data['answer'] ?? '',
                $data['lessonId'] ?? null
            );
            
            return $this->json($exercise, Response::HTTP_CREATED);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    #[Route('/{id}', name: 'exercise_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = $this->decodeJsonRequest($request);
            
            $exercise = $this->exerciseService->updateExercise(
                $id,
                $data['question'] ?? null,
                $data['type'] ?? null,
                $data['options'] ?? null,
                $data['answer'] ?? null
            );
            
            if (!$exercise) {
                return $this->createNotFoundResponse('Exercice');
            }
            
            return $this->json($exercise);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    #[Route('/{id}', name: 'exercise_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $success = $this->exerciseService->deleteExercise($id);
        
        if (!$success) {
            return $this->createNotFoundResponse('Exercice');
        }
        
        return $this->json(['message' => 'Exercice supprimÃ©'], Response::HTTP_NO_CONTENT);
    }
}