<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

class CheckoutService
{
    private OrderRepository $orderRepo;
    private CartRepository $cartRepo;
    private ProductRepository $productRepo;

    public function __construct(
        OrderRepository $orderRepo,
        CartRepository $cartRepo,
        ProductRepository $productRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->cartRepo = $cartRepo;
        $this->productRepo = $productRepo;
    }

    public function createOrder(string $sessionId, array $customerData): Order
    {
        $this->validateCustomerData($customerData);

        // Get cart items
        $cartItems = $this->cartRepo->getBySessionId($sessionId);
        if (empty($cartItems)) {
            throw new \Exception("Cart is empty.");
        }

        // Validate stock and calculate total
        $orderItems = [];
        $totalAmount = 0;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();
            if (!$product) {
                throw new \Exception("Product not found for cart item.");
            }

            // Check stock availability
            if ($product->getStock() < $cartItem->getQuantity()) {
                throw new \Exception(
                    "Insufficient stock for {$product->getName()}. Available: {$product->getStock()}."
                );
            }

            $subtotal = $product->getPrice() * $cartItem->getQuantity();
            $totalAmount += $subtotal;

            $orderItems[] = [
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'product_price' => $product->getPrice(),
                'quantity' => $cartItem->getQuantity(),
                'subtotal' => $subtotal
            ];
        }

        // Create order
        $order = new Order(
            0,
            $sessionId,
            $customerData['name'],
            $customerData['email'],
            $customerData['address'],
            $totalAmount,
            'pending',
            $customerData['phone'] ?? null
        );
        $order->setItems($orderItems);

        // Save order (this will also update stock if needed)
        $order = $this->orderRepo->create($order);

        // Update product stock
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();
            $newStock = $product->getStock() - $cartItem->getQuantity();
            $product->setStock($newStock);
            $this->productRepo->update($product);
        }

        // Clear cart after successful order
        $this->cartRepo->clearCart($sessionId);

        return $order;
    }

    public function getOrder(int $orderId): ?Order
    {
        return $this->orderRepo->getById($orderId);
    }

    public function getOrdersBySession(string $sessionId): array
    {
        return $this->orderRepo->getBySessionId($sessionId);
    }

    private function validateCustomerData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Customer name is required.");
        }

        if (empty($data['email'])) {
            throw new \InvalidArgumentException("Customer email is required.");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address.");
        }

        if (empty($data['address'])) {
            throw new \InvalidArgumentException("Shipping address is required.");
        }
    }
}

