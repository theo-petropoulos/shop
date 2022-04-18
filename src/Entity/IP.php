<?php

namespace App\Entity;

use App\Repository\IPRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

#[ORM\Entity(repositoryClass: IPRepository::class)]
class IP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

    #[ORM\Column(type: "string", length: 20, unique: true)]
    private ?string $address;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "IP")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(name: "failed_logins", type: "integer", nullable: true)]
    private ?int $failedLogins;

    #[ORM\Column(name: "count_users", type: "integer", nullable: true)]
    private ?int $countUsers;

    #[ORM\Column(name: "blacklist", type: "boolean", nullable: true)]
    private ?bool $blacklist;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFailedLogins(): ?int
    {
        return $this->failedLogins;
    }

    public function setFailedLogins(?int $failedLogins): self
    {
        $this->failedLogins = $failedLogins;

        return $this;
    }

    public function getCountUsers(): ?int
    {
        return $this->countUsers;
    }

    public function setCountUsers(?int $countUsers): self
    {
        $this->countUsers = $countUsers;

        return $this;
    }

    public function getBlacklist(): ?bool
    {
        return $this->blacklist;
    }

    public function setBlacklist(?bool $blacklist): self
    {
        $this->blacklist = $blacklist;

        return $this;
    }

    #[Pure]
    public function belongsToUser(User $user): bool
    {
        return $this->getUser() === $user;
    }
}
