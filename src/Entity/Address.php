<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Exceptions\InvalidSizeException;
use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;
use function PHPUnit\Framework\throwException;

#[ApiResource(denormalizationContext: ["disable_type_enforcement"=>true])]
#[ORM\Entity(repositoryClass: AddressRepository::class)]

class Address
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "addresses")]
    #[ORM\JoinColumn(nullable: false, onDelete: "cascade")]
    #[Assert\NotBlank(message: "Le champ 'customer' est obligatoire.")]
    private ?User $customer;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du nom de famille est obligatoire")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaine de caractères valides.")]
    #[Assert\Length(max: 33, maxMessage: "Le nom ne doit pas excéder {{limit}} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/", message: "Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $lastName;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du prénom est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaine de caractères valide.")]
    #[Assert\Length(max: 33, maxMessage: "Le nom ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/", message: "Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $firstName;

    #[ORM\Column(type: "string", length: 15, nullable: true)]
    #[Assert\Length(max: 10, maxMessage: "Le numéro d'adresse ne peut pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\p{N}\s,.']+$/",message: "Le numéro d'adresse ne peut contenir que des lettres et des chiffres.")]
    private ?string $streetNumber;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le champ de l'adresse est obligatoire.")]
    #[Assert\Length(
        min: 8, max:255,
        minMessage: "L'adresse doit comporter au moins {{ limit }} caractères.",
        maxMessage: "L'adresse doit ne doit pas excéder {{ limit }} caractères."
    )]
    #[Assert\Regex(pattern:"/^[\p{L}\p{N}\s,.'-]+$/", message: "L'adresse ne peut contenir que des lettres, des chiffres, des apostrophes, des points et des tirets.")]
    private ?string $streetName;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Type(type: "string", message: "Le complément d'adresse doit être une chaine de caractères valides.")]
    #[Assert\Regex(pattern: "/^[a-z0-9 ,.'-]+$/i", message: "Le complément d'adresse ne peut contenir que des lettres, des chiffres, des apostrophes, des points et des tirets.")]
    private ?string $streetAddition;

    #[Column(type: "integer", length: 5)]
    #[Assert\NotBlank(message: 'Le champ du code postal est obligatoire.')]
    #[Assert\Type(type: 'numeric',message: 'Le code postal ne doit contenir que des chiffres.')]
    #[Assert\Length(min: 4, max: 5, minMessage: 'Le code postal doit contenir exactement {{limit}} caractères. Pour un département étranger, utilisez le code 99999', maxMessage: 'Le code postal doit contenir exactement {{limit}} caractères. Pour un département étranger, utilisez le code 99999')]
    #[Assert\Range(notInRangeMessage: 'Le code postal doit être compris entre {{ min }} et {{ max }.', min: 1000, max: 99999)]
    private ?int $postalCode;

    #[ORM\Column(type: "string", length: 80)]
    #[Assert\NotBlank(message: "Le champ de la ville est obligatoire.")]
    #[Assert\Type(type: "string",message: "La ville doit être une chaine de caractères valides.")]
    #[Assert\Length(min: 3, max: 80, minMessage: "La ville doit contneir au moins {{ limit }} caractères.", maxMessage: "La ville ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[\p{L}\s'-]+$/i", message: "La ville ne peut contenir que des lettres, des apostrophes et des tirets.")]
    private ?string $city;

    #[ORM\Column(type: "boolean", nullable: true)]
    #[Assert\Type(type: "bool", message: "La valeur main doit être de type {{ type }}.")]
    private ?bool $main;

    public function __construct($user = null)
    {
        if ($user) $this->customer = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ?User
     */
    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    /**
     * @param User|null $customer
     * @return self
     */
    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string
     * @return self
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string
     * @return self
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    /**
     * @param string|null $streetNumber
     * @return self
     */
    public function setStreetNumber(?string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    /**
     * @param string
     * @return self
     */
    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getStreetAddition(): ?string
    {
        return $this->streetAddition;
    }

    /**
     * @param string|null $streetAddition
     * @return self
     */
    public function setStreetAddition(?string $streetAddition): self
    {
        $this->streetAddition = $streetAddition;

        return $this;
    }

    /**
     * @return ?int
     */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    /**
     * @param int
     * @return self
     * @throws InvalidSizeException
     */
    public function setPostalCode(int $postalCode): self
    {
        if (strlen((string) $postalCode) > 5 || strlen((string) $postalCode) < 4)
            throw new InvalidSizeException("Le champ du code postal ne peut contenir que 4 ou 5 caractères");
        else {
            $this->postalCode = $postalCode;
            return $this;
        }
    }

    /**
     * @return ?string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string
     * @return self
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return ?bool
     */
    public function getMain(): ?bool
    {
        return $this->main;
    }

    /**
     * @param bool|null $main
     * @return self
     */
    public function setMain(?bool $main): self
    {
        $this->main = $main;

        return $this;
    }
}
