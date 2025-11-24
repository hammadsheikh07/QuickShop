<?php

namespace App\Controllers;

use App\Services\ProductService;
use App\Models\Product;

class ProductController
{
    private ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));

        // Remove 'api', 'products', and 'products.php' from path to get the ID if any
        $pathParts = array_filter($pathParts, function($part) {
            return $part !== 'api' 
                && $part !== 'products' 
                && $part !== 'products.php'
                && !empty($part);
        });
        
        // Re-index array after filtering
        $pathParts = array_values($pathParts);

        header('Content-Type: application/json');

        try {
            switch ($method) {
                case 'GET':
                    if (empty($pathParts)) {
                        $this->getAll();
                    } else {
                        $id = (int) reset($pathParts);
                        // Only call getById if ID is valid (greater than 0)
                        if ($id > 0) {
                            $this->getById($id);
                        } else {
                            $this->getAll();
                        }
                    }
                    break;

                case 'POST':
                    $this->create();
                    break;

                case 'PUT':
                case 'PATCH':
                    $id = (int) reset($pathParts);
                    $this->update($id);
                    break;

                case 'DELETE':
                    $id = (int) reset($pathParts);
                    $this->delete($id);
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

    private function getAll(): void
    {
        $products = $this->service->listProducts();
        $data = array_map(fn(Product $p) => [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'stock' => $p->getStock()
        ], $products);

        echo json_encode($data);
    }

    private function getById(int $id): void
    {
        $product = $this->service->getProduct($id);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        echo json_encode([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock()
        ]);
    }

    private function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        $product = $this->service->createProduct($data);
        
        http_response_code(201);
        echo json_encode([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock()
        ]);
    }

    private function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }

        $success = $this->service->updateProduct($id, $data);
        
        if ($success) {
            $product = $this->service->getProduct($id);
            echo json_encode([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update product']);
        }
    }

    private function delete(int $id): void
    {
        $success = $this->service->deleteProduct($id);
        
        if ($success) {
            http_response_code(204);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    }
}

