<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(fields: ['username'], name: 'unique_username')]
#[ORM\UniqueConstraint(fields: ['email'], name: 'unique_email')]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Ajoute les groupes pour chaque propriété
    #[ORM\Column(length: 20)]
    #[Groups(['user:read', 'user:write', 'progression:read', 'progression:write'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'progression:read', 'progression:write'])]
    private ?string $email = null;

    #[Groups(['user:write'])] // <-- seulement pour l'écriture, pas pour la lecture
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[Groups(['user:read', "user:write"])] // <-- seulement pour la lecture, pas pour l'écriture
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ChatMessage::class, orphanRemoval: true)]
    #[Groups(['user:read'])]
    private Collection $chatMessages;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'host', targetEntity: GameSession::class)]
    #[Groups(['user:read'])]
    private Collection $hostedGames;

    #[ORM\OneToMany(mappedBy: 'guest', targetEntity: GameSession::class)]
    #[Groups(['user:read'])]
    private Collection $joinedGames;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Progression::class, orphanRemoval: true)]
    #[Groups(['user:read'])]
    private Collection $progressions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Leaderboard::class, orphanRemoval: true)]
    #[Groups(['user:read'])]
    private Collection $leaderboardEntries;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';
    
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
    
        return $this;
    }

    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in modern versions of PHP
        return null;
    }

    public function eraseCredentials(): void
    {
        
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function __construct()
    {
        $this->progressions = new ArrayCollection();
        $this->chatMessages = new ArrayCollection();
        $this->hostedGames = new ArrayCollection();
        $this->joinedGames = new ArrayCollection();
        $this->leaderboardEntries = new ArrayCollection();
    }

    public function getProgressions(): Collection
    {
        return $this->progressions;
    }   

    public function addProgression(Progression $progression): self
    {
        if (!$this->progressions->contains($progression)) {
            $this->progressions[] = $progression;
            $progression->setUser($this);
        }

        return $this;
    }

    public function removeProgression(Progression $progression): self
    {
        if ($this->progressions->removeElement($progression)) {
            if ($progression->getUser() === $this) {
                $progression->setUser(null);
            }
        }

        return $this;
    }

    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): static
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages->add($chatMessage);
            $chatMessage->setUser($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): static
    {
        if ($this->chatMessages->removeElement($chatMessage)) {
            if ($chatMessage->getUser() === $this) {
                $chatMessage->setUser(null);
            }
        }

        return $this;
    }

    public function getHostedGames(): Collection
    {
        return $this->hostedGames;
    }

    public function addHostedGame(GameSession $game): static
    {
        if (!$this->hostedGames->contains($game)) {
            $this->hostedGames->add($game);
            $game->setHost($this);
        }

        return $this;
    }

    public function removeHostedGame(GameSession $game): static
    {
        if ($this->hostedGames->removeElement($game)) {
            if ($game->getHost() === $this) {
                $game->setHost(null);
            }
        }

        return $this;
    }

    public function getJoinedGames(): Collection
    {
        return $this->joinedGames;
    }

    public function addJoinedGame(GameSession $game): static
    {
        if (!$this->joinedGames->contains($game)) {
            $this->joinedGames->add($game);
            $game->setGuest($this);
        }

        return $this;
    }

    public function removeJoinedGame(GameSession $game): static
    {
        if ($this->joinedGames->removeElement($game)) {
            if ($game->getGuest() === $this) {
                $game->setGuest(null);
            }
        }

        return $this;
    }

    public function getLeaderboardEntries(): Collection
    {
        return $this->leaderboardEntries;
    }

    public function addLeaderboardEntry(Leaderboard $leaderboard): static
    {
        if (!$this->leaderboardEntries->contains($leaderboard)) {
            $this->leaderboardEntries->add($leaderboard);
            $leaderboard->setUser($this);
        }

        return $this;
    }

    public function removeLeaderboardEntry(Leaderboard $leaderboard): static
    {
        if ($this->leaderboardEntries->removeElement($leaderboard)) {
            if ($leaderboard->getUser() === $this) {
                $leaderboard->setUser(null);
            }
        }

        return $this;
    }
}
