<?php

namespace App\Repositories;

use PDO;
use App\Models\Product;

class ProductRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Fetch all products
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Product(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['price'],
            $row['stock']
        ), $rows);
    }

    // Fetch product by ID
    public function getById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return new Product(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['price'],
            $row['stock']
        );
    }

    // Insert new product
    public function create(Product $p): Product
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, description, price, stock)
            VALUES (:name, :description, :price, :stock)
        ");

        $stmt->execute([
            'name' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'stock' => $p->getStock(),
        ]);

        $p->setId($this->db->lastInsertId());
        return $p;
    }

    // Update product
    public function update(Product $p): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET name = :name, description = :description, price = :price, stock = :stock
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $p->getId(),
            'name' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'stock' => $p->getStock()
        ]);
    }

    // Delete product by ID
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
