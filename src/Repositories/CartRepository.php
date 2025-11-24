<?php

namespace App\Repositories;

use PDO;
use App\Models\CartItem;
use App\Models\Product;

class CartRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getBySessionId(string $sessionId): array
    {
        $stmt = $this->db->prepare("
            SELECT ci.*, p.name, p.description, p.price, p.stock, p.deleted_at
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.id
            WHERE ci.session_id = :session_id
            ORDER BY ci.created_at DESC
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($row) {
            $cartItem = new CartItem(
                $row['id'],
                $row['session_id'],
                $row['product_id'],
                $row['quantity']
            );
            $product = new Product(
                $row['product_id'],
                $row['name'],
                $row['description'],
                $row['price'],
                $row['stock']
            );
            $cartItem->setProduct($product);
            // Mark product as deleted if deleted_at is set
            if (isset($row['deleted_at']) && $row['deleted_at'] !== null) {
                $product->setStock(0); // Set stock to 0 to indicate unavailable
            }
            return $cartItem;
        }, $rows);
    }

    public function getBySessionAndProduct(string $sessionId, int $productId): ?CartItem
    {
        $stmt = $this->db->prepare("
            SELECT ci.*, p.name, p.description, p.price, p.stock, p.deleted_at
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.id
            WHERE ci.session_id = :session_id AND ci.product_id = :product_id
        ");
        $stmt->execute([
            'session_id' => $sessionId,
            'product_id' => $productId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $cartItem = new CartItem(
            $row['id'],
            $row['session_id'],
            $row['product_id'],
            $row['quantity']
        );
        $product = new Product(
            $row['product_id'],
            $row['name'],
            $row['description'],
            $row['price'],
            $row['stock']
        );
        // Mark product as deleted if deleted_at is set
        if (isset($row['deleted_at']) && $row['deleted_at'] !== null) {
            $product->setStock(0); // Set stock to 0 to indicate unavailable
        }
        $cartItem->setProduct($product);
        return $cartItem;
    }

    public function addItem(string $sessionId, int $productId, int $quantity): CartItem
    {
        $existing = $this->getBySessionAndProduct($sessionId, $productId);
        
        if ($existing) {
            $newQuantity = $existing->getQuantity() + $quantity;
            $this->updateQuantity($existing->getId(), $newQuantity);
            return $this->getBySessionAndProduct($sessionId, $productId);
        }

        $stmt = $this->db->prepare("
            INSERT INTO cart_items (session_id, product_id, quantity)
            VALUES (:session_id, :product_id, :quantity)
        ");
        $stmt->execute([
            'session_id' => $sessionId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->getBySessionAndProduct($sessionId, $productId);
    }

    public function updateQuantity(int $cartItemId, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cart_items
            SET quantity = :quantity
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $cartItemId,
            'quantity' => $quantity
        ]);
    }

    public function removeItem(int $cartItemId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = :id");
        $stmt->execute(['id' => $cartItemId]);
        return $stmt->rowCount() > 0;
    }

    public function clearCart(string $sessionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE session_id = :session_id");
        $stmt->execute(['session_id' => $sessionId]);
        return true;
    }

    public function getCartCount(string $sessionId): int
    {
        $stmt = $this->db->prepare("
            SELECT SUM(quantity) as total
            FROM cart_items
            WHERE session_id = :session_id
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }
}

