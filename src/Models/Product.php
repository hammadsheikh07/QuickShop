<?php

namespace App\Models;

class Product
{
    public int $id;
    public string $name;
    public string $description;
    public float $price;
    public int $stock;

    public function __construct(
        int $id,
        string $name,
        string $description,
        float $price,
        int $stock
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
    }
}
