<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *  denormalizationContext={
 *      "disable_type_enforcement"=true
 *  }
 * )
 * @ORM\Entity(repositoryClass=BrandRepository::class)
 */
class Brand
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="boolean", nullable=true)
     * @Assert\Type(
     *  type="bool",
     *  message="Le champ active doit être de type {{ type }}."
     * )
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="brand")
     */
    private $products;

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

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBrand($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getBrand() === $this) {
                $product->setBrand(null);
            }
        }

        return $this;
    }
}
