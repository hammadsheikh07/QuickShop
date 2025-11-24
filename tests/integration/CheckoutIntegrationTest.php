<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Models\Product;

class CheckoutIntegrationTest extends TestCase
{
    private PDO $db;
    private OrderRepository $orderRepo;
    private CartRepository $cartRepo;
    private ProductRepository $productRepo;
    private CartService $cartService;
    private CheckoutService $checkoutService;

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

        $this->db->exec("
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                customer_name TEXT NOT NULL,
                customer_email TEXT NOT NULL,
                customer_phone TEXT,
                shipping_address TEXT NOT NULL,
                total_amount REAL NOT NULL,
                status TEXT DEFAULT 'pending',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->db->exec("
            CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT NOT NULL,
                product_price REAL NOT NULL,
                quantity INTEGER NOT NULL,
                subtotal REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");

        $this->productRepo = new ProductRepository($this->db);
        $this->cartRepo = new CartRepository($this->db);
        $this->orderRepo = new OrderRepository($this->db);
        $this->cartService = new CartService($this->cartRepo, $this->productRepo);
        $this->checkoutService = new CheckoutService($this->orderRepo, $this->cartRepo, $this->productRepo);
    }

    public function test_full_stack_create_order()
    {
        // Create products
        $product1 = $this->productRepo->create(new Product(0, "Product 1", "Desc", 100.0, 10));
        $product2 = $this->productRepo->create(new Product(0, "Product 2", "Desc", 50.0, 10));
        
        // Add to cart
        $this->cartService->addToCart("session1", $product1->getId(), 2);
        $this->cartService->addToCart("session1", $product2->getId(), 1);
        
        // Create order
        $order = $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'phone' => '+1234567890'
        ]);
        
        $this->assertNotNull($order->getId());
        $this->assertEquals("John Doe", $order->getCustomerName());
        $this->assertEquals("john@example.com", $order->getCustomerEmail());
        $this->assertEquals("123 Main St", $order->getShippingAddress());
        $this->assertEquals(250.0, $order->getTotalAmount()); // (100 * 2) + (50 * 1)
        $this->assertCount(2, $order->getItems());
    }

    public function test_full_stack_create_order_updates_product_stock()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        
        $this->cartService->addToCart("session1", $product->getId(), 3);
        
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
        
        $updatedProduct = $this->productRepo->getById($product->getId());
        $this->assertEquals(7, $updatedProduct->getStock()); // 10 - 3
    }

    public function test_full_stack_create_order_clears_cart()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        
        $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
        
        $cart = $this->cartService->getCart("session1");
        $this->assertCount(0, $cart);
    }

    public function test_full_stack_create_order_throws_if_cart_empty()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cart is empty.");
        
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_full_stack_create_order_throws_if_insufficient_stock()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 5));
        
        // Add items within stock limit
        $this->cartService->addToCart("session1", $product->getId(), 3);
        
        // Manually reduce stock to simulate race condition or stock change
        $product->setStock(2);
        $this->productRepo->update($product);
        
        // Now try to create order - should fail because cart has 3 but only 2 available
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock for Product. Available: 2.");
        
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_full_stack_create_order_validates_customer_data()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Customer name is required.");
        
        $this->checkoutService->createOrder("session1", [
            'name' => '',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_full_stack_get_order_by_id()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        $this->cartService->addToCart("session1", $product->getId(), 2);
        
        $order = $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
        
        $retrieved = $this->checkoutService->getOrder($order->getId());
        
        $this->assertNotNull($retrieved);
        $this->assertEquals("John Doe", $retrieved->getCustomerName());
        $this->assertCount(1, $retrieved->getItems());
    }

    public function test_full_stack_get_orders_by_session()
    {
        $product = $this->productRepo->create(new Product(0, "Product", "Desc", 100.0, 10));
        
        $this->cartService->addToCart("session1", $product->getId(), 1);
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
        
        $this->cartService->addToCart("session1", $product->getId(), 1);
        $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '456 Main St'
        ]);
        
        $orders = $this->checkoutService->getOrdersBySession("session1");
        
        $this->assertCount(2, $orders);
    }

    public function test_full_stack_order_items_are_persisted_correctly()
    {
        $product1 = $this->productRepo->create(new Product(0, "Product 1", "Desc", 100.0, 10));
        $product2 = $this->productRepo->create(new Product(0, "Product 2", "Desc", 50.0, 10));
        
        $this->cartService->addToCart("session1", $product1->getId(), 2);
        $this->cartService->addToCart("session1", $product2->getId(), 1);
        
        $order = $this->checkoutService->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
        
        $retrieved = $this->checkoutService->getOrder($order->getId());
        
        $this->assertCount(2, $retrieved->getItems());
        $this->assertEquals("Product 1", $retrieved->getItems()[0]['product_name']);
        $this->assertEquals(2, $retrieved->getItems()[0]['quantity']);
        $this->assertEquals(200.0, $retrieved->getItems()[0]['subtotal']);
    }
}

