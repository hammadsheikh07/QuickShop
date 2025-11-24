<?php

namespace App\Models;

class CartItem
{
    private int $id;
    private string $sessionId;
    private int $productId;
    private int $quantity;
    private ?Product $product = null;

    public function __construct(
        int $id,
        string $sessionId,
        int $productId,
        int $quantity
    ) {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getSessionId(): string { return $this->sessionId; }
    public function setSessionId(string $sessionId): void { $this->sessionId = $sessionId; }

    public function getProductId(): int { return $this->productId; }
    public function setProductId(int $productId): void { $this->productId = $productId; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): void { $this->product = $product; }
}

