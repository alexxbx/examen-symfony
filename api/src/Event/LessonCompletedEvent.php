<?php
// src/Event/LessonCompletedEvent.php

namespace App\Event;

use App\Entity\Lesson;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use App\Service\LessonUnlockService;

class LessonCompletedEvent extends Event
{
    public const NAME = 'lesson.completed';
    
    private User $user;
    private Lesson $lesson;
    
    public function __construct(User $user, Lesson $lesson)
    {
        $this->user = $user;
        $this->lesson = $lesson;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function getLesson(): Lesson
    {
        return $this->lesson;
    }
}

// src/EventSubscriber/LessonCompletedSubscriber.php

namespace App\EventSubscriber;

use App\Event\LessonCompletedEvent;
use App\Service\NotificationService;
use App\Service\LessonUnlockService;
use App\Service\ProgressionService;
use App\Service\AchievementService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LessonCompletedSubscriber implements EventSubscriberInterface
{
    private NotificationService $notificationService;
    private LessonUnlockService $lessonUnlockService;
    private ProgressionService $progressionService;
    private AchievementService $achievementService;
    
    public function __construct(
        NotificationService $notificationService,
        LessonUnlockService $lessonUnlockService,
        ProgressionService $progressionService,
        AchievementService $achievementService
    ) {
        $this->notificationService = $notificationService;
        $this->lessonUnlockService = $lessonUnlockService;
        $this->progressionService = $progressionService;
        $this->achievementService = $achievementService;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            LessonCompletedEvent::NAME => 'onLessonCompleted',
        ];
    }
    
    public function onLessonCompleted(LessonCompletedEvent $event): void
    {
        $user = $event->getUser();
        $lesson = $event->getLesson();
        $this->progressionService->unlockNextLesson($user, $lesson);
        $this->notificationService->sendLessonCompletedNotification($user, $lesson);
        $this->achievementService->checkAndUnlockAchievements($user->getId());
    }
}