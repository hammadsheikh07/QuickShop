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

    // Fetch all products (excluding soft-deleted)
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products WHERE deleted_at IS NULL");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Product(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['price'],
            $row['stock']
        ), $rows);
    }

    // Fetch all products including deleted (for admin)
    public function getAllIncludingDeleted(): array
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

    // Fetch product by ID (excluding soft-deleted)
    public function getById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND deleted_at IS NULL");
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

    // Fetch product by ID including deleted (for admin)
    public function getByIdIncludingDeleted(int $id): ?Product
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

    // Check if product is deleted
    public function isDeleted(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT deleted_at FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['deleted_at'] !== null;
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

    // Soft delete product by ID
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // Restore soft-deleted product
    public function restore(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = NULL WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
