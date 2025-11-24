<?php

use PHPUnit\Framework\TestCase;
use App\Services\CartService;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Models\CartItem;
use App\Models\Product;

class CartServiceTest extends TestCase
{
    private $cartRepoMock;
    private $productRepoMock;
    private CartService $service;

    protected function setUp(): void
    {
        $this->cartRepoMock = $this->createMock(CartRepository::class);
        $this->productRepoMock = $this->createMock(ProductRepository::class);
        $this->service = new CartService($this->cartRepoMock, $this->productRepoMock);
    }

    public function test_get_cart()
    {
        $cartItems = [
            new CartItem(1, "session1", 1, 2)
        ];
        $cartItems[0]->setProduct(new Product(1, "Product", "Desc", 99.99, 10));

        $this->cartRepoMock->method('getBySessionId')->willReturn($cartItems);

        $result = $this->service->getCart("session1");

        $this->assertCount(1, $result);
    }

    public function test_add_to_cart_validates_quantity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Quantity must be at least 1.");

        $this->service->addToCart("session1", 1, 0);
    }

    public function test_add_to_cart_throws_if_product_not_found()
    {
        $this->productRepoMock->method('getById')->willReturn(null);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Product not found.");

        $this->service->addToCart("session1", 999, 1);
    }

    public function test_add_to_cart_throws_if_insufficient_stock()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 5);
        $this->productRepoMock->method('getById')->willReturn($product);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock. Available: 5.");

        $this->service->addToCart("session1", 1, 10);
    }

    public function test_add_to_cart_successfully_adds_item()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 10);
        $this->productRepoMock->method('getById')->willReturn($product);
        $this->cartRepoMock->method('getBySessionAndProduct')->willReturn(null);

        $cartItem = new CartItem(1, "session1", 1, 1);
        $cartItem->setProduct($product);
        $this->cartRepoMock->method('addItem')->willReturn($cartItem);

        $result = $this->service->addToCart("session1", 1, 2);

        $this->assertInstanceOf(CartItem::class, $result);
        $this->assertEquals(1, $result->getQuantity());
    }

    public function test_add_to_cart_validates_existing_item_stock()
    {
        $product = new Product(1, "Product", "Desc", 99.99, 5);
        $this->productRepoMock->method('getById')->willReturn($product);

        $existingItem = new CartItem(1, "session1", 1, 3);
        $this->cartRepoMock->method('getBySessionAndProduct')->willReturn($existingItem);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock. Available: 5.");

        $this->service->addToCart("session1", 1, 3); // 3 + 3 = 6, but only 5 available
    }

    public function test_update_quantity_validates_quantity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Quantity must be at least 1.");

        $this->service->updateQuantity("session1", 1, 0);
    }

    public function test_update_quantity_throws_if_cart_item_not_found()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cart item not found.");

        $this->service->updateQuantity("session1", 999, 2);
    }

    public function test_update_quantity_throws_if_insufficient_stock()
    {
        $cartItem = new CartItem(1, "session1", 1, 2);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $product = new Product(1, "Product", "Desc", 99.99, 5);
        $this->productRepoMock->method('getById')->willReturn($product);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient stock. Available: 5.");

        $this->service->updateQuantity("session1", 1, 10);
    }

    public function test_update_quantity_successfully_updates()
    {
        $cartItem = new CartItem(1, "session1", 1, 2);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);

        $product = new Product(1, "Product", "Desc", 99.99, 10);
        $this->productRepoMock->method('getById')->willReturn($product);
        $this->cartRepoMock->method('updateQuantity')->willReturn(true);

        $result = $this->service->updateQuantity("session1", 1, 5);

        $this->assertTrue($result);
    }

    public function test_remove_from_cart_throws_if_item_not_found()
    {
        $this->cartRepoMock->method('getBySessionId')->willReturn([]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cart item not found.");

        $this->service->removeFromCart("session1", 999);
    }

    public function test_remove_from_cart_successfully_removes()
    {
        $cartItem = new CartItem(1, "session1", 1, 2);
        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem]);
        $this->cartRepoMock->method('removeItem')->willReturn(true);

        $result = $this->service->removeFromCart("session1", 1);

        $this->assertTrue($result);
    }

    public function test_get_cart_total()
    {
        $product1 = new Product(1, "Product 1", "Desc", 100.0, 10);
        $product2 = new Product(2, "Product 2", "Desc", 50.0, 10);

        $cartItem1 = new CartItem(1, "session1", 1, 2);
        $cartItem1->setProduct($product1);
        $cartItem2 = new CartItem(2, "session1", 2, 1);
        $cartItem2->setProduct($product2);

        $this->cartRepoMock->method('getBySessionId')->willReturn([$cartItem1, $cartItem2]);

        $total = $this->service->getCartTotal("session1");

        $this->assertEquals(250.0, $total); // (100 * 2) + (50 * 1)
    }

    public function test_get_cart_count()
    {
        $this->cartRepoMock->method('getCartCount')->willReturn(5);

        $count = $this->service->getCartCount("session1");

        $this->assertEquals(5, $count);
    }
}

