<?php

namespace App\Repositories;

use PDO;
use App\Models\Order;

class OrderRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(Order $order): Order
    {
        $this->db->beginTransaction();
        try {
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO orders (session_id, customer_name, customer_email, customer_phone, shipping_address, total_amount, status)
                VALUES (:session_id, :customer_name, :customer_email, :customer_phone, :shipping_address, :total_amount, :status)
            ");
            $stmt->execute([
                'session_id' => $order->getSessionId(),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'customer_phone' => $order->getCustomerPhone(),
                'shipping_address' => $order->getShippingAddress(),
                'total_amount' => $order->getTotalAmount(),
                'status' => $order->getStatus()
            ]);

            $orderId = (int) $this->db->lastInsertId();
            $order->setId($orderId);

            // Insert order items
            foreach ($order->getItems() as $item) {
                $itemStmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
                    VALUES (:order_id, :product_id, :product_name, :product_price, :quantity, :subtotal)
                ");
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_price' => $item['product_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            $this->db->commit();
            return $this->getById($orderId);
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): ?Order
    {
        $stmt = $this->db->prepare("
            SELECT * FROM orders WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        $order = new Order(
            $row['id'],
            $row['session_id'],
            $row['customer_name'],
            $row['customer_email'],
            $row['shipping_address'],
            $row['total_amount'],
            $row['status'],
            $row['customer_phone'],
            $row['created_at']
        );

        // Load order items
        $itemsStmt = $this->db->prepare("
            SELECT * FROM order_items WHERE order_id = :order_id
        ");
        $itemsStmt->execute(['order_id' => $id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        $order->setItems($items);

        return $order;
    }

    public function getBySessionId(string $sessionId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM orders 
            WHERE session_id = :session_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['session_id' => $sessionId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($row) {
            $order = new Order(
                $row['id'],
                $row['session_id'],
                $row['customer_name'],
                $row['customer_email'],
                $row['shipping_address'],
                $row['total_amount'],
                $row['status'],
                $row['customer_phone'],
                $row['created_at']
            );

            // Load order items
            $itemsStmt = $this->db->prepare("
                SELECT * FROM order_items WHERE order_id = :order_id
            ");
            $itemsStmt->execute(['order_id' => $row['id']]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            $order->setItems($items);

            return $order;
        }, $rows);
    }
}

