<?php

namespace App\Entity;

use App\Repository\DiscountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiscountRepository::class)]
class Discount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

    #[ORM\OneToMany(mappedBy: "discount", targetEntity: Product::class, fetch: "EAGER")]
    private Collection $products;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du nom est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le nom doit contenir une chaine de caractères valides.")]
    #[Assert\Length(
        min: 4, max: 155,
        minMessage: "Le champ du nom doit contenir au moins {{ limit }} caractères.", maxMessage: "Le nom ne peut excéder {{ limit }} caractères."
    )]
    private ?string $name;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le champ promotion est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "Le champ promotion doit être de type {{ type }}.")]
    #[Assert\Range(notInRangeMessage: "La promotion doit être au minimum de {{ min }}% et au maximum de {{ max }}%.", min: 1, max: 99)]
    private ?float$percentage;

    #[ORM\Column(type: "date")]
    #[Assert\Type("DateTime")]
    private ?\DateTimeInterface $startingDate;

    #[ORM\Column(type: "date", nullable: true)]
    #[Assert\Type("DateTime")]
    private ?\DateTimeInterface $endingDate;

    #[Pure]
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setDiscount($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getDiscount() === $this) {
                $product->setDiscount(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function setPercentage(float $percentage): self
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getStartingDate(): ?\DateTimeInterface
    {
        return $this->startingDate;
    }

    public function setStartingDate(\DateTimeInterface $startingDate): self
    {
        $this->startingDate = $startingDate;

        return $this;
    }

    public function getEndingDate(): ?\DateTimeInterface
    {
        return $this->endingDate;
    }

    public function setEndingDate(?\DateTimeInterface $endingDate): self
    {
        $this->endingDate = $endingDate;

        return $this;
    }
}
