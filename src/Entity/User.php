<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'login', type: Types::STRING, length: 20)]
    private ?string $username = null;

    #[ORM\Column(name: 'jelszo', type: Types::STRING, length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(name: 'jogok', type: Types::SIMPLE_ARRAY, length: 200)]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(name: 'regdatum', type: Types::DATETIME_IMMUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'lastlogin', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(name: 'lastactive', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastActiveAt = null;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 100)]
    private ?string $email = null;

    #[ORM\Column(name: 'notifications', type: Types::BOOLEAN)]
    private ?bool $notifications = false;

    #[ORM\Column(name: 'becenev', type: Types::STRING, length: 50)]
    private ?string $nickname = null;

    #[ORM\Column(name: 'nev', type: Types::STRING, length: 100)]
    private ?string $fullName = null;

    #[ORM\Column(name: 'volunteer', type: Types::BOOLEAN)]
    private ?bool $volunteer = false;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: true)]
    private ?string $passwordChangeHash = null;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeImmutable
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeImmutable $lastActiveAt): static
    {
        $this->lastActiveAt = $lastActiveAt;

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

    public function isNotifications(): ?bool
    {
        return $this->notifications;
    }

    public function setNotifications(bool $notifications): static
    {
        $this->notifications = $notifications;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function isVolunteer(): ?bool
    {
        return $this->volunteer;
    }

    public function setVolunteer(bool $volunteer): static
    {
        $this->volunteer = $volunteer;

        return $this;
    }

    public function getPasswordChangeHash(): ?string
    {
        return $this->passwordChangeHash;
    }

    public function setPasswordChangeHash(?string $passwordChangeHash): static
    {
        $this->passwordChangeHash = $passwordChangeHash;

        return $this;
    }
}