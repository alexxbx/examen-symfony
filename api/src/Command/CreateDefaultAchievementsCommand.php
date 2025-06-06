<?php

namespace App\Command;

use App\Entity\Achievement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDefaultAchievementsCommand extends Command
{
    protected static $defaultName = 'app:create-achievements';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    public static function getDefaultName(): ?string
    {
        return 'app:create-achievements';
    }

    protected function configure()
    {
        $this->setDescription('Crée les achievements par défaut dans la base de données');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $achievements = [
            [
                'title' => 'Premier pas',
                'description' => 'Complétez votre première leçon',
                'requiredLessons' => 1,
                'icon' => '🎯'
            ],
            [
                'title' => 'Apprenti',
                'description' => 'Complétez 5 leçons',
                'requiredLessons' => 5,
                'icon' => '📚'
            ],
            [
                'title' => 'Étudiant',
                'description' => 'Complétez 10 leçons',
                'requiredLessons' => 10,
                'icon' => '🎓'
            ],
            [
                'title' => 'Maître',
                'description' => 'Complétez 20 leçons',
                'requiredLessons' => 20,
                'icon' => '👨‍🏫'
            ],
            [
                'title' => 'Grand Maître',
                'description' => 'Complétez toutes les leçons',
                'requiredLessons' => 50,
                'icon' => '🏆'
            ]
        ];

        foreach ($achievements as $achievementData) {
            $achievement = new Achievement();
            $achievement->setTitle($achievementData['title']);
            $achievement->setDescription($achievementData['description']);
            $achievement->setRequiredLessons($achievementData['requiredLessons']);
            $achievement->setIcon($achievementData['icon']);

            $this->entityManager->persist($achievement);
        }

        $this->entityManager->flush();

        $output->writeln('Achievements par défaut créés avec succès !');

        return Command::SUCCESS;
    }
} 