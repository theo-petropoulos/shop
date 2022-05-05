<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    protected ?int $id;

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

    #[ORM\Column(type:"boolean", nullable: true)]
    #[Assert\Type(type: "bool", message: "La valeur active doit être de type {{ type }}.")]
    private ?bool $active;

    #[ORM\Column(name: "sells", type:"integer", nullable: true)]
    private int $sells;

    #[ORM\OneToMany(mappedBy: "author", targetEntity: Product::class, fetch: "EAGER")]
    private Collection $products;

    #[ORM\OneToMany(mappedBy: "author", targetEntity: Image::class, cascade: ["persist"])]
    private Collection $images;

    #[Pure]
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getSells(): ?int
    {
        return $this->sells;
    }

    public function addSells(int $sells): self
    {
        $this->sells += $sells;

        return $this;
    }

    public function getLastProduct(): Product
    {
        return $this->products[count($this->products) - 1];
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setAuthor($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getAuthor() === $this) {
                $product->setAuthor(null);
            }
        }

        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAuthor($this);
        }

        return $this;
    }
}
