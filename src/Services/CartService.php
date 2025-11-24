<?php

namespace App\Services;

use App\Models\CartItem;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

class CartService
{
    private CartRepository $cartRepo;
    private ProductRepository $productRepo;

    public function __construct(CartRepository $cartRepo, ProductRepository $productRepo)
    {
        $this->cartRepo = $cartRepo;
        $this->productRepo = $productRepo;
    }

    public function getCart(string $sessionId): array
    {
        return $this->cartRepo->getBySessionId($sessionId);
    }

    public function addToCart(string $sessionId, int $productId, int $quantity = 1): CartItem
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException("Quantity must be at least 1.");
        }

        $product = $this->productRepo->getById($productId);
        if (!$product) {
            throw new \Exception("Product not found.");
        }

        if ($product->getStock() < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$product->getStock()}.");
        }

        $existing = $this->cartRepo->getBySessionAndProduct($sessionId, $productId);
        if ($existing) {
            $newQuantity = $existing->getQuantity() + $quantity;
            if ($product->getStock() < $newQuantity) {
                throw new \Exception("Insufficient stock. Available: {$product->getStock()}.");
            }
        }

        return $this->cartRepo->addItem($sessionId, $productId, $quantity);
    }

    public function updateQuantity(string $sessionId, int $cartItemId, int $quantity): bool
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException("Quantity must be at least 1.");
        }

        // Get the cart item by checking all items in the cart
        $cart = $this->cartRepo->getBySessionId($sessionId);
        $item = null;
        foreach ($cart as $ci) {
            if ($ci->getId() === $cartItemId) {
                $item = $ci;
                break;
            }
        }

        if (!$item) {
            throw new \Exception("Cart item not found.");
        }

        $product = $this->productRepo->getById($item->getProductId());
        if (!$product) {
            throw new \Exception("Product not found.");
        }

        if ($product->getStock() < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$product->getStock()}.");
        }

        return $this->cartRepo->updateQuantity($cartItemId, $quantity);
    }

    public function removeFromCart(string $sessionId, int $cartItemId): bool
    {
        // Verify the item belongs to this session
        $cart = $this->cartRepo->getBySessionId($sessionId);
        $exists = false;
        foreach ($cart as $item) {
            if ($item->getId() === $cartItemId) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            throw new \Exception("Cart item not found.");
        }

        return $this->cartRepo->removeItem($cartItemId);
    }

    public function clearCart(string $sessionId): bool
    {
        return $this->cartRepo->clearCart($sessionId);
    }

    public function getCartCount(string $sessionId): int
    {
        return $this->cartRepo->getCartCount($sessionId);
    }

    public function getCartTotal(string $sessionId): float
    {
        $cart = $this->cartRepo->getBySessionId($sessionId);
        $total = 0;
        foreach ($cart as $item) {
            $product = $item->getProduct();
            if ($product) {
                $total += $product->getPrice() * $item->getQuantity();
            }
        }
        return $total;
    }
}

