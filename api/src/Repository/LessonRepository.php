<?php
// src/Repository/LessonRepository.php

// src/Repository/LessonRepository.php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    /**
     * Récupère les leçons avec leurs exercices en une seule requête
     * 
     * @param array $criteria Les critères de recherche pour les leçons
     * @param array|null $orderBy Ordre de tri des résultats
     * @param int|null $limit Limite du nombre de résultats
     * @param int|null $offset Décalage pour la pagination
     * 
     * @return Lesson[] Liste des leçons avec leurs exercices associés
     */
    public function findWithExercises(array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.exercises', 'e')  // Jointure avec les exercices
            ->addSelect('e');               // Sélectionner les exercices

        // Appliquer les critères de recherche dynamiques
        foreach ($criteria as $field => $value) {
            $qb->andWhere("l.$field = :$field") // Utilisation de l'alias 'l' pour les champs de la leçon
               ->setParameter($field, $value);
        }

        // Appliquer l'ordre de tri
        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy("l.$field", $direction);
            }
        }

        // Appliquer la limite et l'offset pour la pagination
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        // Exécuter la requête et retourner le résultat
        return $qb->getQuery()->getResult();
    }

    /**
     * Récupère toutes les leçons avec leur état de déverrouillage pour un utilisateur donné
     * 
     * @param int $userId L'ID de l'utilisateur
     * 
     * @return array Liste des leçons avec leur progression pour un utilisateur donné
     */
    public function findLessonsWithUnlockStatus(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->select('l', 'p')  // Sélectionner les leçons et les progressions
            ->leftJoin('App\Entity\Progression', 'p', 'WITH', 'p.lesson = l.id AND p.user = :userId') // Jointure avec la table Progression
            ->setParameter('userId', $userId)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNextLesson(Lesson $currentLesson): ?Lesson
{
    return $this->createQueryBuilder('l')
            ->where('l.order > :currentOrder')
            ->setParameter('currentOrder', $currentLesson->getOrder())
            ->orderBy('l.order', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}


}
