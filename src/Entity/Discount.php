<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DiscountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints\DateValidator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=DiscountRepository::class)
 */
class Discount
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="discount")
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=155)
     * @Assert\NotBlank(
     *  message="Le champ du nom est obligatoire."
     * )
     * @Assert\Type(
     *  type="string",
     *  message="Le champ du nom doit contenir une chaine de caractères valides."
     * )
     * @Assert\Length(
     *  min=4,
     *  minMessage="Le champ du nom doit contenir au moins {{ limit }} caractères.",
     *  max=155,
     *  maxMessage="Le champ du nom ne doit pas excéder {{ limit }} messages."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(
     *  message="Le champ promotion est obligatoire."
     * )
     * @Assert\Type(
     *  type="numeric",
     *  message="Le champ promotion doit être de type {{ type }}."
     * )
     * @Assert\Range(
     *  min=1,
     *  max=99,
     *  notInRangeMessage="La promotion doit être au minimum de {{ min }}% et au maximum de {{ max }}%."
     * )
     */
    private $percentage;

    /**
     * @ORM\Column(type="date")
     * @Assert\Type("DateTime")
     */
    private $startingDate;

    /**
     * @ORM\Column(type="date")
     * @Assert\Type("DateTime")
     */
    private $endingDate;

    public function __construct()
    {
        $this->product = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProduct(): Collection
    {
        return $this->product;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->product->contains($product)) {
            $this->product[] = $product;
            $product->setDiscount($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->product->removeElement($product)) {
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

    public function setEndingDate(\DateTimeInterface $endingDate): self
    {
        $this->endingDate = $endingDate;

        return $this;
    }
}
