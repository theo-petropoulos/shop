<?php

namespace App\Entity;

use App\Repository\IPRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IPRepository::class)]
class IP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 20)]
    private ?string $address;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "IP")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

    #[ORM\Column(type: "boolean", nullable: true)]
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

    public function getBlacklist(): ?bool
    {
        return $this->blacklist;
    }

    public function setBlacklist(?bool $blacklist): self
    {
        $this->blacklist = $blacklist;

        return $this;
    }
}
