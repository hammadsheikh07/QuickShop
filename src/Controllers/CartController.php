<?php

namespace App\Controllers;

use App\Services\CartService;
use App\Models\CartItem;

class CartController
{
    private CartService $service;

    public function __construct(CartService $service)
    {
        $this->service = $service;
    }

    public function handleRequest(): void
    {
        $sessionId = $this->getSessionId();
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));

        // Remove 'api', 'cart', 'cart.php', 'public', and other known segments
        $pathParts = array_filter($pathParts, function($part) {
            return $part !== 'api' 
                && $part !== 'cart' 
                && $part !== 'cart.php'
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
                case 'GET':
                    $this->getCart($sessionId);
                    break;

                case 'POST':
                    $this->addItem($sessionId);
                    break;

                case 'PUT':
                case 'PATCH':
                    if ($id && $id > 0) {
                        $this->updateItem($sessionId, $id);
                    } else {
                        http_response_code(400);
                        echo json_encode(['error' => 'Cart item ID is required']);
                    }
                    break;

                case 'DELETE':
                    if ($id && $id > 0) {
                        $this->removeItem($sessionId, $id);
                    } else {
                        $this->clearCart($sessionId);
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

    private function getCart(string $sessionId): void
    {
        $cart = $this->service->getCart($sessionId);
        $total = $this->service->getCartTotal($sessionId);
        $count = $this->service->getCartCount($sessionId);

        $data = array_map(function(CartItem $item) {
            $product = $item->getProduct();
            return [
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
                'product' => $product ? [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'description' => $product->getDescription(),
                    'price' => $product->getPrice(),
                    'stock' => $product->getStock()
                ] : null
            ];
        }, $cart);

        echo json_encode([
            'items' => $data,
            'total' => $total,
            'count' => $count
        ]);
    }

    private function addItem(string $sessionId): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        if (!isset($data['product_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'product_id is required']);
            return;
        }

        $quantity = isset($data['quantity']) ? (int) $data['quantity'] : 1;
        $cartItem = $this->service->addToCart($sessionId, (int) $data['product_id'], $quantity);

        $product = $cartItem->getProduct();
        http_response_code(201);
        echo json_encode([
            'id' => $cartItem->getId(),
            'product_id' => $cartItem->getProductId(),
            'quantity' => $cartItem->getQuantity(),
            'product' => $product ? [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock()
            ] : null
        ]);
    }

    private function updateItem(string $sessionId, int $cartItemId): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        if (!isset($data['quantity'])) {
            http_response_code(400);
            echo json_encode(['error' => 'quantity is required']);
            return;
        }

        $success = $this->service->updateQuantity($sessionId, $cartItemId, (int) $data['quantity']);
        
        if ($success) {
            $this->getCart($sessionId);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update cart item']);
        }
    }

    private function removeItem(string $sessionId, int $cartItemId): void
    {
        $success = $this->service->removeFromCart($sessionId, $cartItemId);
        
        if ($success) {
            http_response_code(204);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Cart item not found']);
        }
    }

    private function clearCart(string $sessionId): void
    {
        $this->service->clearCart($sessionId);
        http_response_code(204);
    }
}

