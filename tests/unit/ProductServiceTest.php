<?php

use PHPUnit\Framework\TestCase;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use App\Models\Product;

class ProductServiceTest extends TestCase
{
    private $repoMock;
    private ProductService $service;

    protected function setUp(): void
    {
        $this->repoMock = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repoMock);
    }

    public function test_list_products()
    {
        $this->repoMock->method('getAll')->willReturn([
            new Product(1, "Phone", "desc", 500, 10)
        ]);

        $products = $this->service->listProducts();

        $this->assertCount(1, $products);
        $this->assertEquals("Phone", $products[0]->getName());
    }

    public function test_create_product_validates_data()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->createProduct([
            "name" => "",
            "price" => 200
        ]);
    }

    public function test_create_product_calls_repository()
    {
        $data = [
            "name" => "Mouse",
            "price" => 20,
            "description" => "Wireless"
        ];

        $this->repoMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(Product::class))
            ->willReturn(new Product(1, "Mouse", "Wireless", 20, 0));

        $result = $this->service->createProduct($data);

        $this->assertEquals(1, $result->getId());
    }

    public function test_update_product_throws_if_not_found()
    {
        $this->repoMock->method('getById')->willReturn(null);

        $this->expectException(Exception::class);

        $this->service->updateProduct(1, [
            "name" => "Updated",
            "price" => 100
        ]);
    }
}
