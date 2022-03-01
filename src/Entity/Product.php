<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  denormalizationContext={
 *      "disable_type_enforcement"=true
 *  }
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Brand::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=155)
     * @Assert\NotBlank(
     *  message="Le champ du nom est obligatoire."
     * )
     * @Assert\Type(
     *  type="string",
     *  message="Le nom doit contenir une chaine de caractères valides."
     * )
     * @Assert\Length(
     *  max=155,
     *  maxMessage="Le nom ne peut excéder {{ limit }} caractères."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=500)
     * @Assert\NotBlank(
     *  message="Le champ description est obligatoire."
     * )
     * @Assert\Type(
     *  type="string",
     *  message="La description doit contenir une chaine de caractères valides."
     * )
     * @Assert\Length(
     *  max=500,
     *  maxMessage="La description ne peut excéder {{ limit }} caractères."
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(
     *  message="Le champ image est obligatoire."
     * )
     * @Assert\Type(
     *  type="string",
     *  message="Le champ image contenir une chaine de caractères valides."
     * )
     * @Assert\Length(
     *  max=255,
     *  maxMessage="Le chemin de l'image ne peut excéder {{ limit }} caractères."
     * )
     */
    private $imagePath;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(
     *  message="Le champ du prix est obligatoire."
     * )
     * @Assert\Type(
     *  type="numeric",
     *  message="Le prix doit être de type {{ type }}."
     * )
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(
     *  message="Le champ stock est obligatoire."
     * )
     * @Assert\Type(
     *  type="numeric",
     *  message="Le stock doit être de type {{ type }}."
     * )
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\Type(
     *  type="bool",
     *  message="La valeur active doit être de type {{ type }}."
     * )
     */
    private $active;

    /**
     * @ORM\ManyToOne(targetEntity=Discount::class, inversedBy="product")
     */
    private $discount;

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

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }
}
