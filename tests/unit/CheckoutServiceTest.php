<?php

use PHPUnit\Framework\TestCase;
use App\Services\CheckoutService;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\Product;

class CheckoutServiceTest extends TestCase
{
    private $orderRepoMock;
    private $cartRepoMock;
    private $productRepoMock;
    private CheckoutService $service;

    protected function setUp(): void
    {
        $this->orderRepoMock = $this->createMock(OrderRepository::class);
        $this->cartRepoMock = $this->createMock(CartRepository::class);
        $this->productRepoMock = $this->createMock(ProductRepository::class);
        $this->service = new CheckoutService(
            $this->orderRepoMock,
            $this->cartRepoMock,
            $this->productRepoMock
        );
    }

    public function test_create_order_validates_customer_name()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Customer name is required.");

        $this->service->createOrder("session1", [
            'name' => '',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_validates_customer_email()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Customer email is required.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => '',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_validates_email_format()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid email address.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_validates_shipping_address()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Shipping address is required.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => ''
        ]);
    }

    public function test_create_order_throws_if_cart_is_empty()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cart is empty.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_throws_if_product_not_found()
    {
        $cartItem = new CartItem(1, "session1", 1, 2);
        $cartItem->setProduct(null);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Product not found for cart item.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_throws_if_insufficient_stock()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 5);
        $cartItem = new CartItem(1, "session1", 1, 10);
        $cartItem->setProduct($product);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock for Product. Available: 5.");

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_successfully_creates_order()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 10);
        $cartItem = new CartItem(1, "session1", 1, 2);
        $cartItem->setProduct($product);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $order = new Order(1, "session1", "John", "john@example.com", "123 St", 199.98);
        $order->setItems([
            [
                'product_id' => 1,
                'product_name' => 'Product',
                'product_price' => 99.99,
                'quantity' => 2,
                'subtotal' => 199.98
            ]
        ]);

        $this->orderRepoMock->method('create')->willReturn($order);
        $this->productRepoMock->method('update')->willReturn(true);
        $this->cartRepoMock->method('clearCart')->willReturn(true);

        $result = $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals(199.98, $result->getTotalAmount());
    }

    public function test_create_order_calculates_total_correctly()
    {
        $product1 = new Product(1, "Product 1", "Desc", 100.0, 10);
        $product2 = new Product(2, "Product 2", "Desc", 50.0, 10);

        $cartItem1 = new CartItem(1, "session1", 1, 2);
        $cartItem1->setProduct($product1);
        $cartItem2 = new CartItem(2, "session1", 2, 1);
        $cartItem2->setProduct($product2);

        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem1, $cartItem2]);

        $order = new Order(1, "session1", "John", "john@example.com", "123 St", 250.0);
        $order->setItems([]);
        $this->orderRepoMock->method('create')->willReturn($order);
        $this->productRepoMock->method('update')->willReturn(true);
        $this->cartRepoMock->method('clearCart')->willReturn(true);

        $result = $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);

        $this->assertEquals(250.0, $result->getTotalAmount());
    }

    public function test_create_order_updates_product_stock()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 10);
        $cartItem = new CartItem(1, "session1", 1, 3);
        $cartItem->setProduct($product);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $order = new Order(1, "session1", "John", "john@example.com", "123 St", 299.97);
        $order->setItems([]);
        $this->orderRepoMock->method('create')->willReturn($order);
        $this->productRepoMock->expects($this->once())->method('update');
        $this->cartRepoMock->method('clearCart')->willReturn(true);

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_create_order_clears_cart_after_success()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 10);
        $cartItem = new CartItem(1, "session1", 1, 2);
        $cartItem->setProduct($product);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $order = new Order(1, "session1", "John", "john@example.com", "123 St", 199.98);
        $order->setItems([]);
        $this->orderRepoMock->method('create')->willReturn($order);
        $this->productRepoMock->method('update')->willReturn(true);
        $this->cartRepoMock->expects($this->once())->method('clearCart')->willReturn(true);

        $this->service->createOrder("session1", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => '123 Main St'
        ]);
    }

    public function test_get_order()
    {
        $order = new Order(1, "session1", "John", "john@example.com", "123 St", 100.0);
        $this->orderRepoMock->method('getById')->willReturn($order);

        $result = $this->service->getOrder(1);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals(1, $result->getId());
    }

    public function test_get_orders_by_session()
    {
        $orders = [
            new Order(1, "session1", "John", "john@example.com", "123 St", 100.0),
            new Order(2, "session1", "John", "john@example.com", "456 St", 200.0)
        ];
        $this->orderRepoMock->method('getBySessionId')->willReturn($orders);

        $result = $this->service->getOrdersBySession("session1");

        $this->assertCount(2, $result);
    }
}

