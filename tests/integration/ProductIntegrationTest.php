<?php

use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use App\Controllers\ProductController;
use App\Models\Product;

class ProductIntegrationTest extends TestCase
{
    private PDO $db;
    private ProductRepository $repository;
    private ProductService $service;
    private ProductController $controller;

    protected function setUp(): void
    {
        // Use in-memory SQLite for integration tests
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create schema
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
        $this->service = new ProductService($this->repository);
        $this->controller = new ProductController($this->service);
    }

    public function test_full_stack_create_and_retrieve_product()
    {
        // Create product through service
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock' => 10
        ];

        $created = $this->service->createProduct($productData);

        $this->assertNotNull($created->getId());
        $this->assertEquals('Test Product', $created->getName());
        $this->assertEquals(99.99, $created->getPrice());

        // Retrieve through repository
        $retrieved = $this->repository->getById($created->getId());
        $this->assertNotNull($retrieved);
        $this->assertEquals('Test Product', $retrieved->getName());
    }

    public function test_full_stack_list_all_products()
    {
        // Create multiple products
        $this->service->createProduct(['name' => 'Product 1', 'price' => 10.0, 'stock' => 5]);
        $this->service->createProduct(['name' => 'Product 2', 'price' => 20.0, 'stock' => 10]);
        $this->service->createProduct(['name' => 'Product 3', 'price' => 30.0, 'stock' => 15]);

        // List through service
        $products = $this->service->listProducts();

        $this->assertCount(3, $products);
        $this->assertEquals('Product 1', $products[0]->getName());
        $this->assertEquals('Product 2', $products[1]->getName());
        $this->assertEquals('Product 3', $products[2]->getName());
    }

    public function test_full_stack_update_product()
    {
        // Create product
        $product = $this->service->createProduct([
            'name' => 'Original Name',
            'description' => 'Original Description',
            'price' => 50.0,
            'stock' => 20
        ]);

        $productId = $product->getId();

        // Update through service
        $updated = $this->service->updateProduct($productId, [
            'name' => 'Updated Name',
            'price' => 75.0,
            'stock' => 15
        ]);

        $this->assertTrue($updated);

        // Verify through repository
        $retrieved = $this->repository->getById($productId);
        $this->assertEquals('Updated Name', $retrieved->getName());
        $this->assertEquals(75.0, $retrieved->getPrice());
        $this->assertEquals(15, $retrieved->getStock());
    }

    public function test_full_stack_delete_product()
    {
        // Create product
        $product = $this->service->createProduct([
            'name' => 'To Delete',
            'price' => 25.0,
            'stock' => 5
        ]);

        $productId = $product->getId();

        // Delete through service
        $deleted = $this->service->deleteProduct($productId);
        $this->assertTrue($deleted);

        // Verify deletion through repository
        $retrieved = $this->repository->getById($productId);
        $this->assertNull($retrieved);
    }

    public function test_full_stack_update_nonexistent_product_throws_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Product not found.');

        $this->service->updateProduct(999, [
            'name' => 'Test',
            'price' => 10.0
        ]);
    }

    public function test_full_stack_create_product_with_validation()
    {
        // Test validation - empty name
        $this->expectException(InvalidArgumentException::class);
        $this->service->createProduct([
            'name' => '',
            'price' => 10.0
        ]);
    }

    public function test_full_stack_create_product_with_negative_price()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->createProduct([
            'name' => 'Test',
            'price' => -10.0
        ]);
    }

    public function test_full_stack_create_product_without_stock_defaults_to_zero()
    {
        $product = $this->service->createProduct([
            'name' => 'No Stock Product',
            'price' => 15.0
        ]);

        $this->assertEquals(0, $product->getStock());
    }

    public function test_full_stack_update_preserves_existing_values()
    {
        $product = $this->service->createProduct([
            'name' => 'Original',
            'description' => 'Original Description',
            'price' => 50.0,
            'stock' => 10
        ]);

        // Update only name and price, description should be preserved
        $this->service->updateProduct($product->getId(), [
            'name' => 'Updated',
            'price' => 60.0
        ]);

        $updated = $this->repository->getById($product->getId());
        $this->assertEquals('Updated', $updated->getName());
        $this->assertEquals('Original Description', $updated->getDescription());
        $this->assertEquals(60.0, $updated->getPrice());
    }
}

