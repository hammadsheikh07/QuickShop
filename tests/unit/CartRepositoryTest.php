<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\CartRepository;
use App\Models\Product;

class CartRepositoryTest extends TestCase
{
    private PDO $db;
    private CartRepository $repo;

    protected function setUp(): void
    {
        // SQLite in-memory database
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create tables
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

        // Insert test product
        $this->db->exec("
            INSERT INTO products (name, description, price, stock)
            VALUES ('Test Product', 'Test Description', 99.99, 10)
        ");

        $this->repo = new CartRepository($this->db);
    }

    public function test_add_item_to_cart()
    {
        $cartItem = $this->repo->addItem("session1", 1, 2);

        $this->assertNotNull($cartItem->getId());
        $this->assertEquals("session1", $cartItem->getSessionId());
        $this->assertEquals(1, $cartItem->getProductId());
        $this->assertEquals(2, $cartItem->getQuantity());
        $this->assertNotNull($cartItem->getProduct());
    }

    public function test_add_existing_item_increments_quantity()
    {
        $this->repo->addItem("session1", 1, 2);
        $cartItem = $this->repo->addItem("session1", 1, 3);

        $this->assertEquals(5, $cartItem->getQuantity());
    }

    public function test_get_cart_by_session_id()
    {
        $this->repo->addItem("session1", 1, 2);
        
        $cart = $this->repo->getBySessionId("session1");

        $this->assertCount(1, $cart);
        $this->assertEquals(1, $cart[0]->getProductId());
        $this->assertEquals(2, $cart[0]->getQuantity());
    }

    public function test_get_cart_returns_empty_array_for_empty_session()
    {
        $cart = $this->repo->getBySessionId("nonexistent");

        $this->assertIsArray($cart);
        $this->assertCount(0, $cart);
    }

    public function test_get_by_session_and_product()
    {
        $this->repo->addItem("session1", 1, 2);
        
        $cartItem = $this->repo->getBySessionAndProduct("session1", 1);

        $this->assertNotNull($cartItem);
        $this->assertEquals(1, $cartItem->getProductId());
        $this->assertEquals(2, $cartItem->getQuantity());
    }

    public function test_get_by_session_and_product_returns_null_if_not_found()
    {
        $cartItem = $this->repo->getBySessionAndProduct("session1", 999);

        $this->assertNull($cartItem);
    }

    public function test_update_quantity()
    {
        $cartItem = $this->repo->addItem("session1", 1, 2);
        
        $updated = $this->repo->updateQuantity($cartItem->getId(), 5);

        $this->assertTrue($updated);
        
        $updatedItem = $this->repo->getBySessionAndProduct("session1", 1);
        $this->assertEquals(5, $updatedItem->getQuantity());
    }

    public function test_remove_item()
    {
        $cartItem = $this->repo->addItem("session1", 1, 2);
        
        $removed = $this->repo->removeItem($cartItem->getId());

        $this->assertTrue($removed);
        
        $cart = $this->repo->getBySessionId("session1");
        $this->assertCount(0, $cart);
    }

    public function test_clear_cart()
    {
        $this->repo->addItem("session1", 1, 2);
        $this->repo->addItem("session1", 1, 1); // Will increment
        
        $cleared = $this->repo->clearCart("session1");

        $this->assertTrue($cleared);
        
        $cart = $this->repo->getBySessionId("session1");
        $this->assertCount(0, $cart);
    }

    public function test_get_cart_count()
    {
        $this->repo->addItem("session1", 1, 2);
        
        // Add another product
        $this->db->exec("
            INSERT INTO products (name, description, price, stock)
            VALUES ('Product 2', 'Desc 2', 49.99, 5)
        ");
        $this->repo->addItem("session1", 2, 3);

        $count = $this->repo->getCartCount("session1");

        $this->assertEquals(5, $count); // 2 + 3
    }

    public function test_get_cart_count_returns_zero_for_empty_cart()
    {
        $count = $this->repo->getCartCount("session1");

        $this->assertEquals(0, $count);
    }
}

