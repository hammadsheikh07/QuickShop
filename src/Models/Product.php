<?php

namespace App\Models;

class Product
{
    private int $id;
    private string $name;
    private string $description;
    private int|float $price;
    private int $stock;

    public function __construct(
        int $id,
        string $name,
        string $description,
        int|float $price,
        int $stock
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $desc): void { $this->description = $desc; }

    public function getPrice(): int|float { return $this->price; }
    public function setPrice(int|float $price): void { $this->price = $price; }

    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): void { $this->stock = $stock; }
}
