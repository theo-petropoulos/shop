<?php

namespace App\Entity;

use App\Repository\ProductRepository;

class Cart
{
    private float $totalPrice;

    private array $cart;

    public function __construct(private ProductRepository $productRepository)
    {
        $this->totalPrice   = 0;
        $this->cart         = [];
    }

    # Vérifie la validité des ids contenus dans le cookie et associe chaque entrée à l'objet et à la quantité
    public function getCartFromCookie(array $arrayCart)
    {
        foreach ($arrayCart as $productId => $quantity) {
            if ($product = $this->productRepository->findOneBy(['id' => $productId])) {
                $this->cart[]     = ['product' => $product, 'quantity' => $quantity];
                $this->totalPrice += $product->getPrice() * $quantity;
            }
        }
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    public function setTotalPrice(?float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }
}