 <?php

use PHPUnit\Framework\TestCase;
use App\Models\Product;

class ProductTest extends TestCase
{
    public function test_product_model_sets_properties()
    {
        $product = new Product(
            1,
            "Laptop",
            "A great laptop",
            1200.00,
            10
        );

        $this->assertEquals(1, $product->getId());
        $this->assertEquals("Laptop", $product->getName());
        $this->assertEquals("A great laptop", $product->getDescription());
        $this->assertEquals(1200.00, $product->getPrice());
        $this->assertEquals(10, $product->getStock());
    }
}
