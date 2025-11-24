<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;

class ProductService
{
    private ProductRepository $repo;

    public function __construct(ProductRepository $repo)
    {
        $this->repo = $repo;
    }

    public function listProducts(): array
    {
        return $this->repo->getAll();
    }

    public function listAllProductsIncludingDeleted(): array
    {
        return $this->repo->getAllIncludingDeleted();
    }

    public function getProduct(int $id): ?Product
    {
        return $this->repo->getById($id);
    }

    public function getProductIncludingDeleted(int $id): ?Product
    {
        return $this->repo->getByIdIncludingDeleted($id);
    }

    public function createProduct(array $data): Product
    {
        $this->validate($data);

        $product = new Product(
            0,
            $data['name'],
            $data['description'] ?? '',
            $data['price'],
            $data['stock'] ?? 0
        );

        return $this->repo->create($product);
    }

    public function updateProduct(int $id, array $data): bool
    {
        $existing = $this->repo->getById($id);
        if (!$existing) {
            throw new \Exception("Product not found.");
        }

        $this->validate($data);

        $existing->setName($data['name']);
        $existing->setDescription($data['description'] ?? $existing->getDescription());
        $existing->setPrice($data['price']);
        if (isset($data['stock'])) {
            $existing->setStock($data['stock']);
        }

        return $this->repo->update($existing);
    }

    public function deleteProduct(int $id): bool
    {
        return $this->repo->delete($id);
    }

    private function validate(array $data)
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Name is required.");
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            throw new \InvalidArgumentException("Price must be numeric.");
        }

        if ($data['price'] < 0) {
            throw new \InvalidArgumentException("Price cannot be negative.");
        }
    }
}
