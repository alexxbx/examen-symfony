<?php

namespace App\Entity;

use App\Repository\GameSessionRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    normalizationContext: ['groups' => ['game:read']],
    denormalizationContext: ['groups' => ['game:write']]
)]
#[ORM\Entity(repositoryClass: GameSessionRepository::class)]
class GameSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['game:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hostedGames')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['game:read', 'game:write'])]
    private ?User $host = null;

    #[ORM\ManyToOne(inversedBy: 'joinedGames')]
    #[Groups(['game:read', 'game:write'])]
    private ?User $guest = null;

    #[ORM\Column(length: 50)]
    #[Groups(['game:read', 'game:write'])]
    private ?string $status = null; // 'WAITING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'

    #[ORM\Column]
    #[Groups(['game:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['game:read'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['game:read'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['game:read', 'game:write'])]
    private ?int $hostScore = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['game:read', 'game:write'])]
    private ?int $guestScore = null;

    #[ORM\ManyToOne]
    #[Groups(['game:read', 'game:write'])]
    private ?User $winner = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['game:read', 'game:write'])]
    private ?array $gameData = null; // Pour stocker les données spécifiques au jeu

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'WAITING';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?User
    {
        return $this->host;
    }

    public function setHost(?User $host): static
    {
        $this->host = $host;
        return $this;
    }

    public function getGuest(): ?User
    {
        return $this->guest;
    }

    public function setGuest(?User $guest): static
    {
        $this->guest = $guest;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function getHostScore(): ?int
    {
        return $this->hostScore;
    }

    public function setHostScore(?int $hostScore): static
    {
        $this->hostScore = $hostScore;
        return $this;
    }

    public function getGuestScore(): ?int
    {
        return $this->guestScore;
    }

    public function setGuestScore(?int $guestScore): static
    {
        $this->guestScore = $guestScore;
        return $this;
    }

    public function getWinner(): ?User
    {
        return $this->winner;
    }

    public function setWinner(?User $winner): static
    {
        $this->winner = $winner;
        return $this;
    }

    public function getGameData(): ?array
    {
        return $this->gameData;
    }

    public function setGameData(?array $gameData): static
    {
        $this->gameData = $gameData;
        return $this;
    }
} 