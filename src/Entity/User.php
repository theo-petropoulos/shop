<?php

namespace App\Entity;

use App\Exceptions\InvalidEmailException;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\NotBlank(message: "Le champ email est obligatoire.")]
    #[Assert\Email(message: "L'adresse '{{ email }}' n'est pas valide.")]
    private ?string $email;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(type: "string")]
    #[PasswordStrength(minStrength: 4, minLength: 8)]
    private string $password;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du nom de famille est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaine de caractères valides.")]
    #[Assert\Length(max: 33, maxMessage: "Le nom ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[a-z ,.'-]+$/i", message: "Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $lastName;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du prénom est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le nom doit être une chaine de caractères valides.")]
    #[Assert\Length(max: 33, maxMessage: "Le nom ne doit pas excéder {{ limit }} caractères.")]
    #[Assert\Regex(pattern: "/^[a-z ,.'-]+$/i", message: "Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.")]
    private ?string $firstName;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank(message: "Le champ du numéro de téléphone est obligatoire.")]
    #[Assert\Length(
        min: 10, max: 10,
        minMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} caractères.",
        maxMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} caractères."
    )]
    private ?string $phone;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "Le champ de la date de création est obligatoire.")]
    private ?\DateTimeInterface $creationDate;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: IP::class, orphanRemoval: true)]
    private Collection $IP;

    #[ORM\OneToMany(mappedBy: "customer", targetEntity: Address::class, orphanRemoval: true)]
    private Collection $addresses;

    #[ORM\OneToMany(mappedBy: "customer", targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->creationDate = new \DateTime('today');
        $this->roles        = ['ROLE_USER'];
        $this->IP           = new ArrayCollection();
        $this->addresses    = new ArrayCollection();
        $this->orders       = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @throws \Exception
     */
    public function setEmail(string $email): self
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
            return $this;
        }
        else throw new InvalidEmailException("L'adresse mail utilisée n'est pas valide.", 403);
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        if (!preg_match("/^[a-z ,.'-]+$/i", $lastName))
            throw new \InvalidArgumentException("Le nom ne peut contenir que des lettres, des apostrophes, des points et des tirets.");
        else {
            $this->lastName = $lastName;
            return $this;
        }
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        if (!preg_match("/^[a-z ,.'-]+$/i", $firstName))
            throw new \InvalidArgumentException("Le prénom ne peut contenir que des lettres, des apostrophes, des points et des tirets.");
        else {
            $this->firstName = $firstName;
            return $this;
        }
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        if (!preg_match("/^[0-9 .-]+$/i", $phone))
            throw new \InvalidArgumentException("Le numéro de téléphone ne peut contenir que des nombres et si besoin des espaces, points, tirets.");
        else{
            $this->phone = str_replace([' ', '-', ',', '.'], '', $phone);
            return $this;
        }
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getIP(): Collection
    {
        return $this->IP;
    }

    public function addIP(IP $iP): self
    {
        if (!$this->IP->contains($iP)) {
            $this->IP[] = $iP;
            $iP->setUser($this);
        }

        return $this;
    }

    public function removeIP(IP $iP): self
    {
        if ($this->IP->removeElement($iP)) {
            // set the owning side to null (unless already changed)
            if ($iP->getUser() === $this) {
                $iP->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setCustomer($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getCustomer() === $this) {
                $address->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
