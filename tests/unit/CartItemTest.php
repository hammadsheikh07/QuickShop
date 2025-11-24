<?php

use PHPUnit\Framework\TestCase;
use App\Models\CartItem;
use App\Models\Product;

class CartItemTest extends TestCase
{
    public function test_cart_item_model_sets_properties()
    {
        $cartItem = new CartItem(
            1,
            "session123",
            5,
            3
        );

        $this->assertEquals(1, $cartItem->getId());
        $this->assertEquals("session123", $cartItem->getSessionId());
        $this->assertEquals(5, $cartItem->getProductId());
        $this->assertEquals(3, $cartItem->getQuantity());
    }

    public function test_cart_item_can_set_and_get_product()
    {
        $cartItem = new CartItem(1, "session123", 5, 2);
        $product = new Product(5, "Laptop", "Description", 999.99, 10);

        $cartItem->setProduct($product);

        $this->assertNotNull($cartItem->getProduct());
        $this->assertEquals("Laptop", $cartItem->getProduct()->getName());
    }

    public function test_cart_item_quantity_can_be_updated()
    {
        $cartItem = new CartItem(1, "session123", 5, 2);
        
        $cartItem->setQuantity(5);
        
        $this->assertEquals(5, $cartItem->getQuantity());
    }

    public function test_cart_item_session_id_can_be_updated()
    {
        $cartItem = new CartItem(1, "session123", 5, 2);
        
        $cartItem->setSessionId("new_session");
        
        $this->assertEquals("new_session", $cartItem->getSessionId());
    }
}

