<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: "products")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand;

    #[ORM\ManyToOne(targetEntity: Discount::class, cascade: ["persist", "remove"], inversedBy: "products")]
    #[ORM\JoinColumn(nullable: true, onDelete: "set null")]
    private ?Discount $discount;

    #[ORM\Column(type: "string", length: 155)]
    #[Assert\NotBlank(message: "Le champ du nom est obligatoire.")]
    #[Assert\Type(type: "string", message: "Le nom doit contenir une chaine de caractères valides.")]
    #[Assert\Length(max: 155, maxMessage: "Le nom ne peut excéder {{ limit }} caractères.")]
    private ?string $name;

    #[ORM\Column(type: "string", length: 500)]
    #[Assert\NotBlank(message: "Le champ description est obligatoire.")]
    #[Assert\Type(type: "string", message: "La description doit contenir une chaine de caractères valides.")]
    #[Assert\Length(max: 500, maxMessage: "La description ne peut excéder {{ limit }} caractères.")]
    private ?string $description;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le champ du prix est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "Le prix doit être de type {{ type }}.")]
    private ?float $price;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank(message: "Le champ du stock est obligatoire.")]
    #[Assert\Type(type: "numeric", message: "Le stock doit être de type {{ type }}.")]
    private ?int $stock;

    #[ORM\Column(type:"boolean", nullable: true)]
    #[Assert\Type(type: "bool", message: "La valeur active doit être de type {{ type }}.")]
    private ?bool $active;

    #[ORM\OneToMany(mappedBy: "Product", targetEntity: Image::class, cascade: ["persist"])]
    private Collection $images;

    #[Pure]
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        if ($price >= 0) {
            $this->price = $price;
            return $this;
        }
        else throw new \InvalidArgumentException("Le prix ne peut pas être inférieur à 0.");
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        if ($stock >= 0) {
            $this->stock = $stock;
            return $this;
        }
        else throw new \InvalidArgumentException("Le stock ne peut pas être inférieur à 0.");
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }
}
