<?php

use PHPUnit\Framework\TestCase;
use App\Models\Order;

class OrderTest extends TestCase
{
    public function test_order_model_sets_properties()
    {
        $order = new Order(
            1,
            "session123",
            "John Doe",
            "john@example.com",
            "123 Main St",
            199.99,
            "pending",
            "+1234567890",
            "2024-01-01 12:00:00"
        );

        $this->assertEquals(1, $order->getId());
        $this->assertEquals("session123", $order->getSessionId());
        $this->assertEquals("John Doe", $order->getCustomerName());
        $this->assertEquals("john@example.com", $order->getCustomerEmail());
        $this->assertEquals("123 Main St", $order->getShippingAddress());
        $this->assertEquals(199.99, $order->getTotalAmount());
        $this->assertEquals("pending", $order->getStatus());
        $this->assertEquals("+1234567890", $order->getCustomerPhone());
        $this->assertEquals("2024-01-01 12:00:00", $order->getCreatedAt());
    }

    public function test_order_can_have_null_phone()
    {
        $order = new Order(
            1,
            "session123",
            "John Doe",
            "john@example.com",
            "123 Main St",
            199.99
        );

        $this->assertNull($order->getCustomerPhone());
    }

    public function test_order_can_set_and_get_items()
    {
        $order = new Order(1, "session123", "John", "john@example.com", "123 St", 100.0);
        
        $items = [
            ['product_id' => 1, 'product_name' => 'Product 1', 'quantity' => 2, 'subtotal' => 50.0],
            ['product_id' => 2, 'product_name' => 'Product 2', 'quantity' => 1, 'subtotal' => 50.0]
        ];
        
        $order->setItems($items);
        
        $this->assertCount(2, $order->getItems());
        $this->assertEquals('Product 1', $order->getItems()[0]['product_name']);
    }

    public function test_order_status_can_be_updated()
    {
        $order = new Order(1, "session123", "John", "john@example.com", "123 St", 100.0, "pending");
        
        $order->setStatus("completed");
        
        $this->assertEquals("completed", $order->getStatus());
    }
}

