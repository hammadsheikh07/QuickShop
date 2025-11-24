<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\ProductRepository;
use App\Models\Product;

class ProductRepositoryTest extends TestCase
{
    private PDO $db;
    private ProductRepository $repo;

    protected function setUp(): void
    {
        // SQLite in-memory database
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create fake products table
        $this->db->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                description TEXT,
                price REAL,
                stock INTEGER,
                deleted_at TIMESTAMP NULL
            );
        ");

        $this->repo = new ProductRepository($this->db);
    }

    public function test_create_product()
    {
        $product = new Product(0, "Laptop", "Powerful laptop", 999.0, 15);

        $created = $this->repo->create($product);

        $this->assertNotNull($created->getId());
        $this->assertEquals("Laptop", $created->getName());
    }

    public function test_get_by_id_returns_null_if_not_found()
    {
        $result = $this->repo->getById(999);
        $this->assertNull($result);
    }

    public function test_get_all_products()
    {
        $this->repo->create(new Product(0, "A", "Desc A", 10.0, 8));
        $this->repo->create(new Product(0, "B", "Desc B", 20.0, 8));

        $products = $this->repo->getAll();

        $this->assertCount(2, $products);
    }

    public function test_update_product()
    {
        $p = $this->repo->create(new Product(0, "Laptop", "Powerful laptop", 999.0, 15));

        $p->setName("New Name");
        $updated = $this->repo->update($p);

        $this->assertTrue($updated);

        $dbProduct = $this->repo->getById($p->getId());
        $this->assertEquals("New Name", $dbProduct->getName());
    }

    public function test_delete_product_soft_delete()
    {
        $p = $this->repo->create(new Product(0, "Laptop", "Powerful laptop", 999.0, 15));

        $deleted = $this->repo->delete($p->getId());

        $this->assertTrue($deleted);
        // Should not be retrievable via getById (soft delete)
        $this->assertNull($this->repo->getById($p->getId()));
        // But should be retrievable via getByIdIncludingDeleted
        $deletedProduct = $this->repo->getByIdIncludingDeleted($p->getId());
        $this->assertNotNull($deletedProduct);
        $this->assertTrue($this->repo->isDeleted($p->getId()));
    }
}
