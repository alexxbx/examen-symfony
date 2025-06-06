<?php

namespace App\Event;

use App\Entity\Achievement;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AchievementUnlockedEvent extends Event
{
    public const NAME = 'achievement.unlocked';
    
    private User $user;
    private Achievement $achievement;
    
    public function __construct(User $user, Achievement $achievement)
    {
        $this->user = $user;
        $this->achievement = $achievement;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function getAchievement(): Achievement
    {
        return $this->achievement;
    }
} 