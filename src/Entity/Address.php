<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "addresses")]
    #[ORM\JoinColumn(nullable: true, onDelete: "set null")]
    #[Assert\NotBlank(message: "Le champ 'customer' est obligatoire.")]
    private ?User $customer;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du nom de famille est obligatoire")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaine de caractères valides.")]
    #[Assert\Length(max: 33, maxMessage: "Le nom ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/u", message: "Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $lastName;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du prénom est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le prénom doit être une chaine de caractères valide.")]
    #[Assert\Length(max: 33, maxMessage: "Le prénom ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/u", message: "Le prénom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $firstName;

    #[ORM\Column(type: "string", length: 15, nullable: true)]
    #[Assert\Length(max: 10, maxMessage: "Le numéro d'adresse ne peut pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\p{N}\s,.']+$/u",message: "Le numéro d'adresse ne peut contenir que des lettres et des chiffres.")]
    private ?string $streetNumber;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le champ de l'adresse est obligatoire.")]
    #[Assert\Length(min: 8, max:255, minMessage: "L'adresse doit comporter au moins {{ limit }} caractères.", maxMessage: "L'adresse doit ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern:"/^[\p{L}\p{N}\s,.'-]+$/u", message: "L'adresse ne peut contenir que des lettres, des chiffres, des apostrophes, des points et des tirets.")]
    private ?string $streetName;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Type(type: "string", message: "Le complément d'adresse doit être une chaine de caractères valides.")]
    #[Assert\Regex(pattern: "/^[\p{L}\p{N}\s,.'-]+$/ui", message: "Le complément d'adresse ne peut contenir que des lettres, des chiffres, des apostrophes, des points et des tirets.")]
    private ?string $streetAddition;

    #[Column(type: "integer", length: 5)]
    #[Assert\NotBlank(message: 'Le champ du code postal est obligatoire.')]
    #[Assert\Type(type: 'numeric', message: 'Le code postal ne doit contenir que des chiffres.')]
    #[Assert\Length(min: 4, max: 5, minMessage: 'Le code postal doit contenir exactement {{ limit }} caractères. Pour un département étranger, utilisez le code 99999', maxMessage: 'Le code postal doit contenir exactement {{ limit }} caractères. Pour un département étranger, utilisez le code 99999')]
    #[Assert\Range(notInRangeMessage: 'Le code postal doit être compris entre {{ min }} et {{ max }}.', min: 1000, max: 99999)]
    private ?int $postalCode;

    #[ORM\Column(type: "string", length: 80)]
    #[Assert\NotBlank(message: "Le champ de la ville est obligatoire.")]
    #[Assert\Type(type: "string", message: "La ville doit être une chaine de caractères valides.")]
    #[Assert\Length(min: 3, max: 80, minMessage: "La ville doit contenir au moins {{ limit }} caractères.", maxMessage: "La ville ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/ui", message: "La ville ne peut contenir que des lettres, des apostrophes et des tirets.")]
    private ?string $city;

    #[ORM\Column(type: "boolean", nullable: true)]
    #[Assert\Type(type: "bool", message: "La valeur main doit être de type {{ type }}.")]
    private ?bool $main;

    private bool $deletable = true;

    public function __construct($user = null)
    {
        if ($user)
            $this->customer = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetAddition(): ?string
    {
        return $this->streetAddition;
    }

    public function setStreetAddition(?string $streetAddition): self
    {
        $this->streetAddition = $streetAddition;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(mixed $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getMain(): ?bool
    {
        return $this->main;
    }

    public function setMain(?bool $main): self
    {
        $this->main = $main;

        return $this;
    }

    public function setDeletable(bool $deletable): void
    {
        $this->deletable = $deletable;
    }

    public function isDeletable(): bool
    {
        return $this->deletable;
    }
}
