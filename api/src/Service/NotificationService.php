<?php
// src/Service/NotificationService.php

namespace App\Service;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Notification générique
     */
    public function notify(User $user, string $message): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setMessage($message);
        $notification->setIsRead(false);

        $this->em->persist($notification);
        $this->em->flush();
    }

    /**
     * Notification spécifique à une leçon terminée
     */
    public function sendLessonCompletedNotification(User $user, Lesson $lesson): void
    {
        if (!$user) {
            throw new \Exception('Utilisateur non trouvé');
        }
    
        $notification = new Notification();
        $notification->setUser($user); // C'est ici que l'erreur se produit si $user est null
        $notification->setMessage("Vous avez terminé la leçon : " . $lesson->getTitle());
        $notification->setType("lesson_completed");
    
        $this->em->persist($notification);
        $this->em->flush();
    }

    public function sendAchievementUnlockedNotification(User $user, string $achievementTitle, string $achievementDescription): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle('Nouveau succès débloqué ! 🏆');
        $notification->setMessage("Félicitations ! Vous avez débloqué le succès : " . $achievementTitle . " - " . $achievementDescription);
        $notification->setType('achievement_unlocked');
        $notification->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }
}
