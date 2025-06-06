<?php

namespace App\Entity;

use App\Repository\LeaderboardRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['leaderboard:read']],
    denormalizationContext: ['groups' => ['leaderboard:write']]
)]
#[ORM\Entity(repositoryClass: LeaderboardRepository::class)]
class Leaderboard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['leaderboard:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'leaderboardEntries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?int $totalGames = 0;

    #[ORM\Column]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?int $wins = 0;

    #[ORM\Column]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?int $losses = 0;

    #[ORM\Column]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?int $draws = 0;

    #[ORM\Column]
    #[Groups(['leaderboard:read', 'leaderboard:write'])]
    private ?int $totalPoints = 0;

    #[ORM\Column]
    #[Groups(['leaderboard:read'])]
    private ?\DateTimeImmutable $lastUpdated = null;

    public function __construct()
    {
        $this->lastUpdated = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getTotalGames(): ?int
    {
        return $this->totalGames;
    }

    public function setTotalGames(int $totalGames): static
    {
        $this->totalGames = $totalGames;
        return $this;
    }

    public function getWins(): ?int
    {
        return $this->wins;
    }

    public function setWins(int $wins): static
    {
        $this->wins = $wins;
        return $this;
    }

    public function getLosses(): ?int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): static
    {
        $this->losses = $losses;
        return $this;
    }

    public function getDraws(): ?int
    {
        return $this->draws;
    }

    public function setDraws(int $draws): static
    {
        $this->draws = $draws;
        return $this;
    }

    public function getTotalPoints(): ?int
    {
        return $this->totalPoints;
    }

    public function setTotalPoints(int $totalPoints): static
    {
        $this->totalPoints = $totalPoints;
        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }

    public function updateStats(string $result): void
    {
        $this->totalGames++;
        $this->lastUpdated = new \DateTimeImmutable();

        switch ($result) {
            case 'WIN':
                $this->wins++;
                $this->totalPoints += 3;
                break;
            case 'LOSS':
                $this->losses++;
                break;
            case 'DRAW':
                $this->draws++;
                $this->totalPoints += 1;
                break;
        }
    }
} 