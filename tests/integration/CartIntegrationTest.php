<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Models\Product;

class CartIntegrationTest extends TestCase
{
    private PDO $db;
    private CartRepository $cartRepo;
    private ProductRepository $productRepo;
    private CartService $cartService;

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
                stock INTEGER NOT NULL DEFAULT 0
            )
        ");

        $this->db->exec("
            CREATE TABLE cart_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE(session_id, product_id)
            )
        ");

        $this->productRepo = new ProductRepository($this->db);
        $this->cartRepo = new CartRepository($this->db);
        $this->cartService = new CartService($this->cartRepo, $this->productRepo);
    }

    public function test_full_stack_add_to_cart()
    {
        // Create product
        $product = $this->productRepo->create(new Product(0, "Laptop", "Powerful laptop", 999.99, 10));
        
        // Add to cart
        $cartItem = $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $this->assertNotNull($cartItem->getId());
        $this->assertEquals($product->getId(), $cartItem->getProductId());
        $this->assertEquals(2, $cartItem->getQuantity());
        $this->assertNotNull($cartItem->getProduct());
    }

    public function test_full_stack_add_to_cart_throws_if_insufficient_stock()
    {
        $product = $this->productRepo->create(new Product(0, "Laptop", "Desc", 999.99, 5));
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock. Available: 5.");
        
        $this->cartService->addToCart("session1", $product->getId(), 10);
    }

    public function test_full_stack_get_cart()
    {
        $product1 = $this->productRepo->create(new Product(0, "Product 1", "Desc", 100.0, 10));
        $product2 = $this->productRepo->create(new Product(0, "Product 2", "Desc", 50.0, 10));
        
        $this->cartService->addToCart("session1", $product1->getId(), 2);
        $this->cartService->addToCart("session1", $product2->getId(), 1);
        
        $cart = $this->cartService->getCart("session1");
        
        $this->assertCount(2, $cart);
    }

    public function test_full_stack_update_quantity()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        $cartItem = $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $updated = $this->cartService->updateQuantity("session1", $cartItem->getId(), 5);
        
        $this->assertTrue($updated);
        
        $cart = $this->cartService->getCart("session1");
        $this->assertEquals(5, $cart[0]->getQuantity());
    }

    public function test_full_stack_update_quantity_throws_if_insufficient_stock()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 5));
        $cartItem = $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock. Available: 5.");
        
        $this->cartService->updateQuantity("session1", $cartItem->getId(), 10);
    }

    public function test_full_stack_remove_from_cart()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        $cartItem = $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $removed = $this->cartService->removeFromCart("session1", $cartItem->getId());
        
        $this->assertTrue($removed);
        
        $cart = $this->cartService->getCart("session1");
        $this->assertCount(0, $cart);
    }

    public function test_full_stack_get_cart_total()
    {
        $product1 = $this->productRepo->create(new Product(0, "Product 1", "Desc", 100.0, 10));
        $product2 = $this->productRepo->create(new Product(0, "Product 2", "Desc", 50.0, 10));
        
        $this->cartService->addToCart("session1", $product1->getId(), 2);
        $this->cartService->addToCart("session1", $product2->getId(), 1);
        
        $total = $this->cartService->getCartTotal("session1");
        
        $this->assertEquals(250.0, $total); // (100 * 2) + (50 * 1)
    }

    public function test_full_stack_get_cart_count()
    {
        $product1 = $this->productRepo->create(new Product(0, "Product 1", "Desc", 100.0, 10));
        $product2 = $this->productRepo->create(new Product(0, "Product 2", "Desc", 50.0, 10));
        
        $this->cartService->addToCart("session1", $product1->getId(), 2);
        $this->cartService->addToCart("session1", $product2->getId(), 3);
        
        $count = $this->cartService->getCartCount("session1");
        
        $this->assertEquals(5, $count);
    }

    public function test_full_stack_add_existing_product_increments_quantity()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        
        $this->cartService->addToCart("session1", $product->getId(), 2);
        $this->cartService->addToCart("session1", $product->getId(), 3);
        
        $cart = $this->cartService->getCart("session1");
        
        $this->assertCount(1, $cart);
        $this->assertEquals(5, $cart[0]->getQuantity());
    }

    public function test_full_stack_clear_cart()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $cleared = $this->cartService->clearCart("session1");
        
        $this->assertTrue($cleared);
        
        $cart = $this->cartService->getCart("session1");
        $this->assertCount(0, $cart);
    }
}

