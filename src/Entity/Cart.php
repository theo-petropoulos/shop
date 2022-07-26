<?php

namespace App\Entity;

use App\Repository\ProductRepository;

class Cart
{
    private float $totalPrice;

    private int $itemCount;

    private array $cart;

    public function __construct(private ProductRepository $productRepository)
    {
        $this->totalPrice   = 0;
        $this->itemCount    = 0;
        $this->cart         = [];
    }

    # Vérifie la validité des ids contenus dans le cookie et associe chaque entrée à l'objet et à la quantité
    public function getCartFromCookie(array $arrayCart)
    {
        foreach ($arrayCart as $productId => $quantity) {
            /** @var Product|null $product */
            if (($product = $this->productRepository->findOneBy(['id' => $productId])) && $product->getStock() >= $quantity) {
                $this->cart[]       = ['product' => $product, 'quantity' => $quantity];
                $this->totalPrice   += $product->getPrice() * $quantity;
                $this->itemCount    += $quantity;
            }
        }
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    /**
     * @param float|null $totalPrice
     *
     * @return self
     */
    public function setTotalPrice(?float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    /**
     * @return int
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     *
     * @return self
     */
    public function setItemCount(int $itemCount): self
    {
        $this->itemCount = $itemCount;

        return $this;
    }
}