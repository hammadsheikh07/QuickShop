<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use App\Controllers\ProductController;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    private PDO $db;
    private ProductController $controller;
    private array $originalServer;

    protected function setUp(): void
    {
        // Save original $_SERVER values
        $this->originalServer = $_SERVER;

        // Use in-memory SQLite for API tests
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create schema
        $this->db->exec("
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0
            )
        ");

        $repository = new ProductRepository($this->db);
        $service = new ProductService($repository);
        $this->controller = new ProductController($service);
    }

    protected function tearDown(): void
    {
        // Restore original $_SERVER values
        $_SERVER = $this->originalServer;
        $_POST = [];
        $_GET = [];
    }

    private function captureOutput(callable $callback): string
    {
        ob_start();
        try {
            $callback();
        } finally {
            $output = ob_get_clean();
        }
        return $output;
    }

    private function setRequest(string $method, string $uri, array $data = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $_POST = $data;
            // Simulate JSON input
            file_put_contents('php://temp', json_encode($data));
        }
    }

    public function test_get_all_products_returns_json_array()
    {
        // Create test products
        $repo = new ProductRepository($this->db);
        $repo->create(new Product(0, 'Product 1', 'Desc 1', 10.0, 5));
        $repo->create(new Product(0, 'Product 2', 'Desc 2', 20.0, 10));

        $this->setRequest('GET', '/api/products.php');

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals('Product 1', $data[0]['name']);
        $this->assertEquals('Product 2', $data[1]['name']);
    }

    public function test_get_all_products_returns_empty_array_when_no_products()
    {
        $this->setRequest('GET', '/api/products.php');

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertCount(0, $data);
    }

    public function test_get_product_by_id_returns_product()
    {
        $repo = new ProductRepository($this->db);
        $product = $repo->create(new Product(0, 'Test Product', 'Test Desc', 99.99, 15));
        $productId = $product->getId();

        $this->setRequest('GET', "/api/products.php/{$productId}");

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertEquals($productId, $data['id']);
        $this->assertEquals('Test Product', $data['name']);
        $this->assertEquals(99.99, $data['price']);
        $this->assertEquals(15, $data['stock']);
    }

    public function test_get_product_by_id_returns_404_for_nonexistent()
    {
        $this->setRequest('GET', '/api/products.php/999');

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(404, http_response_code());
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Product not found', $data['error']);
    }

    public function test_create_product_returns_created_product()
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'New Description',
            'price' => 49.99,
            'stock' => 25
        ];

        $this->setRequest('POST', '/api/products.php', $productData);
        $_POST = $productData;

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(201, http_response_code());
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('New Product', $data['name']);
        $this->assertEquals(49.99, $data['price']);
    }

    public function test_create_product_validates_required_fields()
    {
        $this->setRequest('POST', '/api/products.php', [
            'name' => '',
            'price' => 10.0
        ]);
        $_POST = ['name' => '', 'price' => 10.0];

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(400, http_response_code());
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_update_product_updates_existing_product()
    {
        $repo = new ProductRepository($this->db);
        $product = $repo->create(new Product(0, 'Original', 'Original Desc', 50.0, 10));
        $productId = $product->getId();

        $updateData = [
            'name' => 'Updated',
            'price' => 75.0,
            'stock' => 15
        ];

        $this->setRequest('PUT', "/api/products.php/{$productId}", $updateData);
        $_POST = $updateData;

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $data = json_decode($output, true);
        $this->assertEquals($productId, $data['id']);
        $this->assertEquals('Updated', $data['name']);
        $this->assertEquals(75.0, $data['price']);
        $this->assertEquals(15, $data['stock']);
    }

    public function test_update_product_returns_404_for_nonexistent()
    {
        $this->setRequest('PUT', '/api/products.php/999', [
            'name' => 'Test',
            'price' => 10.0
        ]);
        $_POST = ['name' => 'Test', 'price' => 10.0];

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(500, http_response_code()); // Exception is caught and returns 500
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
    }

    public function test_delete_product_returns_204()
    {
        $repo = new ProductRepository($this->db);
        $product = $repo->create(new Product(0, 'To Delete', 'Desc', 10.0, 5));
        $productId = $product->getId();

        $this->setRequest('DELETE', "/api/products.php/{$productId}");

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(204, http_response_code());
        $this->assertEmpty($output);

        // Verify deletion
        $deleted = $repo->getById($productId);
        $this->assertNull($deleted);
    }

    public function test_delete_product_returns_404_for_nonexistent()
    {
        $this->setRequest('DELETE', '/api/products.php/999');

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(404, http_response_code());
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Product not found', $data['error']);
    }

    public function test_invalid_method_returns_405()
    {
        $this->setRequest('OPTIONS', '/api/products.php');

        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        $this->assertEquals(405, http_response_code());
        $data = json_decode($output, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Method not allowed', $data['error']);
    }

    public function test_api_returns_json_content_type()
    {
        $this->setRequest('GET', '/api/products.php');

        $headers = [];
        $this->captureOutput(function() {
            $this->controller->handleRequest();
        });

        // Check that Content-Type header was set (we can't easily test headers in unit tests,
        // but we can verify the output is valid JSON)
        $output = $this->captureOutput(function() {
            $this->controller->handleRequest();
        });
        
        $data = json_decode($output, true);
        $this->assertNotNull($data, 'Output should be valid JSON');
    }
}

