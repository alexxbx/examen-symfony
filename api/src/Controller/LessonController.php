<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Progression;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProgressionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/lessons')]
class LessonController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'lesson_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $lessons = $em->getRepository(Lesson::class)->findAll();
        return $this->json($lessons, Response::HTTP_OK, [], ['groups' => 'lesson:read']);
    }
    #[Route('/{id}', name: 'lesson_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $lesson = $em->getRepository(Lesson::class)->find($id);
        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($lesson, Response::HTTP_OK, [], ['groups' => 'lesson:read']);
    }

    #[Route('', name: 'lesson_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
        return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }   
    
        $lesson = new Lesson();
        $lesson->setTitle($data['title'] ?? '');
        $lesson->setContent($data['content'] ?? '');
        $lesson->setLevel($data['level'] ?? 'Débutant');
    
        $errors = $validator->validate($lesson);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
    
        $em->persist($lesson);
        $em->flush();
    
        return $this->json($lesson, 200, [], ['groups' => 'lesson:read']);
    }

    #[Route('/{id}', name: 'lesson_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $lesson = $em->getRepository(Lesson::class)->find($id);
        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $lesson->setTitle($data['title'] ?? $lesson->getTitle());
        $lesson->setContent($data['content'] ?? $lesson->getContent());
        $lesson->setLevel($data['difficulty'] ?? $lesson->getLevel());

        $errors = $this->validator->validate($lesson);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->flush();

        return $this->json($lesson);
    }

    #[Route('/{id}', name: 'lesson_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $lesson = $em->getRepository(Lesson::class)->find($id);
        if (!$lesson) {
            return $this->json(['error' => 'Leçon introuvable'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($lesson);
        $em->flush();

        return $this->json(['message' => 'Leçon supprimée'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/user/{id}/lessons-unlocked', name: 'api_user_lessons_unlocked', methods: ['GET'])]
public function getUnlockedLessons(int $id, ProgressionRepository $progressionRepository): JsonResponse
{
    $progressions = $progressionRepository->findUnlockedLessonsByUser($id);

    $lessons = array_map(function (Progression $progression) {
        $lesson = $progression->getLesson();
        return [
            'id' => $lesson->getId(),
            'title' => $lesson->getTitle(),
            'content' => $lesson->getContent(),
            'order' => $lesson->getOrder(),
            'isUnlocked' => $progression->isUnlocked(),
            'completed' => $progression->isCompleted(),
        ];
    }, $progressions);

    return $this->json($lessons);
}

}
