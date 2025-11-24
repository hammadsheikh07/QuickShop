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

        $this->assertEquals(1, $product->id);
        $this->assertEquals("Laptop", $product->name);
        $this->assertEquals("A great laptop", $product->description);
        $this->assertEquals(1200.00, $product->price);
        $this->assertEquals(10, $product->stock);
    }
}
