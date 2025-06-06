<?php

namespace App\EventSubscriber;

use App\Event\AchievementUnlockedEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AchievementUnlockedSubscriber implements EventSubscriberInterface
{
    private NotificationService $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            AchievementUnlockedEvent::NAME => 'onAchievementUnlocked',
        ];
    }
    
    public function onAchievementUnlocked(AchievementUnlockedEvent $event): void
    {
        $user = $event->getUser();
        $achievement = $event->getAchievement();
        
        $this->notificationService->sendAchievementUnlockedNotification(
            $user,
            $achievement->getTitle(),
            $achievement->getDescription()
        );
    }
} 