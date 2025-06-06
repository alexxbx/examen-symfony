<?php
// src/Service/ExerciseService.php
namespace App\Service;

use App\Entity\Lesson;
use App\Entity\Exercise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExerciseService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }
    
    /**
     * Récupère tous les exercices
     */
    public function getAllExercises(): array
    {
        return $this->entityManager->getRepository(Exercise::class)->findAll();
    }
    
    /**
     * Récupère un exercice par son ID
     */
    public function getExerciseById(int $id): ?Exercise
    {
        return $this->entityManager->getRepository(Exercise::class)->find($id);
    }
    
    /**
     * Crée un nouvel exercice
     */
    public function createExercise(
        string $question,
        string $type,
        array $options,
        string $answer,
        ?int $lessonId
    ): Exercise {
        $lesson = null;
        if ($lessonId) {
            $lesson = $this->entityManager->getRepository(Lesson::class)->find($lessonId);
            if (!$lesson) {
                throw new \InvalidArgumentException('Leçon non trouvée');
            }
        }
        
        $exercise = new Exercise();
        $exercise->setQuestion($question);
        $exercise->setType($type);
        $exercise->setOptions($options);
        $exercise->setAnswer($answer);
        $exercise->setLesson($lesson);
        
        $errors = $this->validator->validate($exercise);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }
        
        $this->entityManager->persist($exercise);
        $this->entityManager->flush();
        
        return $exercise;
    }
    
    /**
     * Met à jour un exercice existant
     */
    public function updateExercise(
        int $id,
        ?string $question,
        ?string $type,
        ?array $options,
        ?string $answer
    ): ?Exercise {
        $exercise = $this->getExerciseById($id);
        
        if (!$exercise) {
            return null;
        }
        
        if ($question !== null) {
            $exercise->setQuestion($question);
        }
        
        if ($type !== null) {
            $exercise->setType($type);
        }
        
        if ($options !== null) {
            $exercise->setOptions($options);
        }
        
        if ($answer !== null) {
            $exercise->setAnswer($answer);
        }
        
        $errors = $this->validator->validate($exercise);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errorMessages));
        }
        
        $this->entityManager->flush();
        
        return $exercise;
    }
    
    /**
     * Supprime un exercice par son ID
     */
    public function deleteExercise(int $id): bool
    {
        $exercise = $this->getExerciseById($id);
        
        if (!$exercise) {
            return false;
        }
        
        $this->entityManager->remove($exercise);
        $this->entityManager->flush();
        
        return true;
    }
}