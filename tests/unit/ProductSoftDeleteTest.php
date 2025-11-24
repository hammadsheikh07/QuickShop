<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\ProductRepository;
use App\Models\Product;

class ProductSoftDeleteTest extends TestCase
{
    private PDO $db;
    private ProductRepository $repository;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->db->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                deleted_at TIMESTAMP NULL
            )
        ");

        $this->repository = new ProductRepository($this->db);
    }

    public function test_get_all_excludes_deleted()
    {
        $product1 = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $product2 = new Product(0, 'Product 2', 'Description 2', 20.00, 10);
        
        $created1 = $this->repository->create($product1);
        $created2 = $this->repository->create($product2);
        
        // Soft delete product 1
        $this->repository->delete($created1->getId());
        
        $products = $this->repository->getAll();
        
        $this->assertCount(1, $products);
        $this->assertEquals($created2->getId(), $products[0]->getId());
    }

    public function test_get_all_including_deleted()
    {
        $product1 = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $product2 = new Product(0, 'Product 2', 'Description 2', 20.00, 10);
        
        $created1 = $this->repository->create($product1);
        $created2 = $this->repository->create($product2);
        
        // Soft delete product 1
        $this->repository->delete($created1->getId());
        
        $products = $this->repository->getAllIncludingDeleted();
        
        $this->assertCount(2, $products);
    }

    public function test_get_by_id_excludes_deleted()
    {
        $product = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $created = $this->repository->create($product);
        
        // Soft delete
        $this->repository->delete($created->getId());
        
        $retrieved = $this->repository->getById($created->getId());
        
        $this->assertNull($retrieved);
    }

    public function test_get_by_id_including_deleted()
    {
        $product = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $created = $this->repository->create($product);
        
        // Soft delete
        $this->repository->delete($created->getId());
        
        $retrieved = $this->repository->getByIdIncludingDeleted($created->getId());
        
        $this->assertNotNull($retrieved);
        $this->assertEquals($created->getId(), $retrieved->getId());
    }

    public function test_is_deleted()
    {
        $product = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $created = $this->repository->create($product);
        
        $this->assertFalse($this->repository->isDeleted($created->getId()));
        
        $this->repository->delete($created->getId());
        
        $this->assertTrue($this->repository->isDeleted($created->getId()));
    }

    public function test_soft_delete_preserves_data()
    {
        $product = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $created = $this->repository->create($product);
        $productId = $created->getId();
        
        // Soft delete
        $this->repository->delete($productId);
        
        // Verify data still exists in database
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotNull($row);
        $this->assertEquals('Product 1', $row['name']);
        $this->assertNotNull($row['deleted_at']);
    }

    public function test_restore_product()
    {
        $product = new Product(0, 'Product 1', 'Description 1', 10.00, 5);
        $created = $this->repository->create($product);
        $productId = $created->getId();
        
        // Soft delete
        $this->repository->delete($productId);
        $this->assertTrue($this->repository->isDeleted($productId));
        
        // Restore
        $this->repository->restore($productId);
        $this->assertFalse($this->repository->isDeleted($productId));
        
        // Should be retrievable again
        $retrieved = $this->repository->getById($productId);
        $this->assertNotNull($retrieved);
    }
}

