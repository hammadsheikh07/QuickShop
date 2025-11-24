<?php

namespace App\Controllers;

use App\Services\CheckoutService;
use App\Models\Order;

class CheckoutController
{
    private CheckoutService $service;

    public function __construct(CheckoutService $service)
    {
        $this->service = $service;
    }

    public function handleRequest(): void
    {
        $sessionId = $this->getSessionId();
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));

        // Remove 'api', 'checkout', 'checkout.php', 'public', and other known segments
        $pathParts = array_filter($pathParts, function($part) {
            return $part !== 'api' 
                && $part !== 'checkout' 
                && $part !== 'checkout.php'
                && $part !== 'public'
                && !empty($part);
        });
        $pathParts = array_values($pathParts);
        
        // If we have remaining parts, try to find a numeric ID (usually the last numeric part)
        $id = null;
        for ($i = count($pathParts) - 1; $i >= 0; $i--) {
            if (is_numeric($pathParts[$i])) {
                $id = (int) $pathParts[$i];
                break;
            }
        }

        header('Content-Type: application/json');

        try {
            switch ($method) {
                case 'POST':
                    $this->createOrder($sessionId);
                    break;

                case 'GET':
                    if ($id && $id > 0) {
                        $this->getOrder($id);
                    } else {
                        $this->getOrders($sessionId);
                    }
                    break;

                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Method not allowed']);
            }
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function getSessionId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = session_id();
        }
        return $_SESSION['cart_session_id'];
    }

    private function createOrder(string $sessionId): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        $order = $this->service->createOrder($sessionId, $data);

        http_response_code(201);
        echo json_encode([
            'id' => $order->getId(),
            'session_id' => $order->getSessionId(),
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_phone' => $order->getCustomerPhone(),
            'shipping_address' => $order->getShippingAddress(),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'created_at' => $order->getCreatedAt(),
            'items' => $order->getItems()
        ]);
    }

    private function getOrder(int $orderId): void
    {
        $order = $this->service->getOrder($orderId);
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        echo json_encode([
            'id' => $order->getId(),
            'session_id' => $order->getSessionId(),
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_phone' => $order->getCustomerPhone(),
            'shipping_address' => $order->getShippingAddress(),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'created_at' => $order->getCreatedAt(),
            'items' => $order->getItems()
        ]);
    }

    private function getOrders(string $sessionId): void
    {
        $orders = $this->service->getOrdersBySession($sessionId);
        
        $data = array_map(function(Order $order) {
            return [
                'id' => $order->getId(),
                'session_id' => $order->getSessionId(),
                'customer_name' => $order->getCustomerName(),
                'customer_email' => $order->getCustomerEmail(),
                'customer_phone' => $order->getCustomerPhone(),
                'shipping_address' => $order->getShippingAddress(),
                'total_amount' => $order->getTotalAmount(),
                'status' => $order->getStatus(),
                'created_at' => $order->getCreatedAt(),
                'items' => $order->getItems()
            ];
        }, $orders);

        echo json_encode($data);
    }
}

