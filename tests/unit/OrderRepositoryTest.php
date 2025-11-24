<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\OrderRepository;
use App\Models\Order;

class OrderRepositoryTest extends TestCase
{
    private PDO $db;
    private OrderRepository $repo;

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

        $this->repo = new OrderRepository($this->db);
    }

    public function test_create_order()
    {
        $order = new Order(
            0,
            "session1",
            "John Doe",
            "john@example.com",
            "123 Main St",
            199.99,
            "pending",
            "+1234567890"
        );

        $order->setItems([
            [
                'product_id' => 1,
                'product_name' => 'Product 1',
                'product_price' => 99.99,
                'quantity' => 2,
                'subtotal' => 199.98
            ]
        ]);

        $created = $this->repo->create($order);

        $this->assertNotNull($created->getId());
        $this->assertEquals("John Doe", $created->getCustomerName());
        $this->assertCount(1, $created->getItems());
    }

    public function test_get_order_by_id()
    {
        $order = new Order(0, "session1", "John", "john@example.com", "123 St", 100.0);
        $order->setItems([
            [
                'product_id' => 1,
                'product_name' => 'Product 1',
                'product_price' => 100.0,
                'quantity' => 1,
                'subtotal' => 100.0
            ]
        ]);

        $created = $this->repo->create($order);
        $retrieved = $this->repo->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertEquals("John", $retrieved->getCustomerName());
        $this->assertCount(1, $retrieved->getItems());
    }

    public function test_get_order_by_id_returns_null_if_not_found()
    {
        $order = $this->repo->getById(999);

        $this->assertNull($order);
    }

    public function test_get_orders_by_session_id()
    {
        $order1 = new Order(0, "session1", "John", "john@example.com", "123 St", 100.0);
        $order1->setItems([]);
        $this->repo->create($order1);

        $order2 = new Order(0, "session1", "John", "john@example.com", "456 St", 200.0);
        $order2->setItems([]);
        $this->repo->create($order2);

        $orders = $this->repo->getBySessionId("session1");

        $this->assertCount(2, $orders);
    }

    public function test_get_orders_by_session_returns_empty_array_for_nonexistent_session()
    {
        $orders = $this->repo->getBySessionId("nonexistent");

        $this->assertIsArray($orders);
        $this->assertCount(0, $orders);
    }

    public function test_create_order_with_multiple_items()
    {
        $order = new Order(0, "session1", "John", "john@example.com", "123 St", 250.0);
        $order->setItems([
            [
                'product_id' => 1,
                'product_name' => 'Product 1',
                'product_price' => 100.0,
                'quantity' => 1,
                'subtotal' => 100.0
            ],
            [
                'product_id' => 2,
                'product_name' => 'Product 2',
                'product_price' => 150.0,
                'quantity' => 1,
                'subtotal' => 150.0
            ]
        ]);

        $created = $this->repo->create($order);

        $this->assertCount(2, $created->getItems());
    }

    public function test_order_items_are_persisted_correctly()
    {
        $order = new Order(0, "session1", "John", "john@example.com", "123 St", 199.98);
        $order->setItems([
            [
                'product_id' => 1,
                'product_name' => 'Test Product',
                'product_price' => 99.99,
                'quantity' => 2,
                'subtotal' => 199.98
            ]
        ]);

        $created = $this->repo->create($order);
        $retrieved = $this->repo->getById($created->getId());

        $this->assertEquals('Test Product', $retrieved->getItems()[0]['product_name']);
        $this->assertEquals(2, $retrieved->getItems()[0]['quantity']);
        $this->assertEquals(199.98, $retrieved->getItems()[0]['subtotal']);
    }
}

