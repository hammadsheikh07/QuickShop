# Test Coverage Documentation

## Overview

QuickShop has comprehensive test coverage using PHPUnit, with both unit tests and integration tests. The test suite ensures reliability and correctness across all major components of the e-commerce application.

## Test Framework

- **Framework**: PHPUnit
- **Configuration**: `phpunit.xml`
- **Test Structure**: 
  - Unit tests: `tests/unit/`
  - Integration tests: `tests/integration/`

## Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run unit tests only
vendor/bin/phpunit tests/unit

# Run integration tests only
vendor/bin/phpunit tests/integration
```

## Unit Test Coverage

### Models (Domain Layer)

#### Product Model (`ProductTest.php`)
- ✅ Property setting and retrieval (id, name, description, price, stock)
- ✅ All getter methods validation

#### CartItem Model (`CartItemTest.php`)
- ✅ Property setting and retrieval (id, session_id, product_id, quantity)
- ✅ Product association (set/get product)
- ✅ Quantity updates
- ✅ Session ID updates

#### Order Model (`OrderTest.php`)
- ✅ Property setting and retrieval (all order properties)
- ✅ Optional fields (phone, created_at)
- ✅ Order items management (set/get items)
- ✅ Order status updates

#### Admin Model (`AdminTest.php`)
- ✅ Admin creation with properties
- ✅ Password hashing and verification
- ✅ Password verification success/failure scenarios

### Services (Business Logic Layer)

#### ProductService (`ProductServiceTest.php`)
- ✅ List products functionality
- ✅ Create product with data validation
- ✅ Create product repository interaction
- ✅ Update product error handling (product not found)
- ✅ Input validation (empty name, invalid data)

#### CartService (`CartServiceTest.php`)
- ✅ Get cart by session ID
- ✅ Add to cart validation (quantity validation)
- ✅ Add to cart error handling (product not found, insufficient stock)
- ✅ Add to cart success scenarios
- ✅ Update quantity validation and error handling
- ✅ Remove from cart functionality
- ✅ Cart total calculation
- ✅ Cart count retrieval
- ✅ Stock validation for existing items

#### CheckoutService (`CheckoutServiceTest.php`)
- ✅ Customer data validation (name, email, address)
- ✅ Email format validation
- ✅ Empty cart validation
- ✅ Product not found validation
- ✅ Insufficient stock validation
- ✅ Order creation success
- ✅ Order total calculation
- ✅ Product stock updates
- ✅ Cart clearing after order
- ✅ Get order by ID
- ✅ Get orders by session ID

#### AuthService (`AuthServiceTest.php`)
- ✅ Successful login
- ✅ Wrong password handling
- ✅ Wrong username handling
- ✅ Authentication status checking
- ✅ Session management (set/get)
- ✅ Logout functionality

### Repositories (Data Access Layer)

#### ProductRepository (`ProductRepositoryTest.php`)
- ✅ Create product
- ✅ Get by ID (returns null if not found)
- ✅ Get all products
- ✅ Update product
- ✅ Soft delete functionality
- ✅ Deleted product retrieval (including deleted)

#### CartRepository (`CartRepositoryTest.php`)
- ✅ Add item to cart
- ✅ Increment quantity for existing items
- ✅ Get cart by session ID
- ✅ Empty cart handling
- ✅ Get by session and product
- ✅ Update quantity
- ✅ Remove item
- ✅ Clear cart
- ✅ Get cart count

#### OrderRepository (`OrderRepositoryTest.php`)
- ✅ Create order with items
- ✅ Get order by ID
- ✅ Get orders by session ID
- ✅ Multiple order items handling
- ✅ Order items persistence verification

#### AdminRepository (`AdminRepositoryTest.php`)
- ✅ Find by username (exists/not exists)
- ✅ Find by ID (exists/not exists)

### Specialized Unit Tests

#### Product Soft Delete (`ProductSoftDeleteTest.php`)
- ✅ Get all excludes deleted products
- ✅ Get all including deleted products
- ✅ Get by ID excludes deleted
- ✅ Get by ID including deleted
- ✅ Is deleted check
- ✅ Soft delete preserves data
- ✅ Restore product functionality

## Integration Test Coverage

### Product Integration (`ProductIntegrationTest.php`)
- ✅ Full stack create and retrieve product (Service → Repository → Database)
- ✅ Full stack list all products
- ✅ Full stack update product
- ✅ Full stack delete product
- ✅ Update nonexistent product error handling
- ✅ Product creation validation
- ✅ Negative price validation
- ✅ Default stock value handling
- ✅ Update preserves existing values

### Cart Integration (`CartIntegrationTest.php`)
- ✅ Full stack add to cart (Service → Repository → Database)
- ✅ Insufficient stock error handling
- ✅ Get cart with multiple products
- ✅ Update quantity
- ✅ Update quantity insufficient stock handling
- ✅ Remove from cart
- ✅ Cart total calculation
- ✅ Cart count calculation
- ✅ Add existing product increments quantity
- ✅ Clear cart functionality

### Checkout Integration (`CheckoutIntegrationTest.php`)
- ✅ Full stack create order (Service → Repository → Database)
- ✅ Order creation updates product stock
- ✅ Order creation clears cart
- ✅ Empty cart error handling
- ✅ Insufficient stock error handling
- ✅ Customer data validation
- ✅ Get order by ID
- ✅ Get orders by session
- ✅ Order items persistence verification

### Admin Authentication Integration (`AdminAuthenticationTest.php`)
- ✅ Complete admin login workflow
- ✅ Product management after login
- ✅ Unauthorized access prevention
- ✅ Admin logout workflow

### Admin Product Management Integration (`AdminProductManagementTest.php`)
- ✅ Admin can create products
- ✅ Admin can update products
- ✅ Admin can soft delete products
- ✅ Deleted products preserve order history
- ✅ Admin can restore deleted products
- ✅ Deleted products not visible to customers
- ✅ Admin view shows all products including deleted

### Product API Integration (`ProductApiTest.php`)
- ✅ GET all products returns JSON array
- ✅ GET all products returns empty array when no products
- ✅ GET product by ID returns product
- ✅ GET product by ID returns 404 for nonexistent
- ✅ POST create product returns created product
- ✅ POST create product validates required fields
- ✅ PUT update product updates existing product
- ✅ PUT update product returns 404 for nonexistent
- ✅ DELETE product returns 204
- ✅ DELETE product returns 404 for nonexistent
- ✅ Invalid HTTP method returns 405
- ✅ API returns JSON content type

## Test Statistics

### Unit Tests
- **Total Unit Test Files**: 13
- **Models Tested**: 4 (Product, CartItem, Order, Admin)
- **Services Tested**: 4 (ProductService, CartService, CheckoutService, AuthService)
- **Repositories Tested**: 4 (ProductRepository, CartRepository, OrderRepository, AdminRepository)
- **Specialized Tests**: 1 (ProductSoftDelete)

### Integration Tests
- **Total Integration Test Files**: 6
- **Full Stack Workflows**: Product, Cart, Checkout, Admin Authentication, Admin Product Management, Product API

## Test Database Strategy

All tests use **SQLite in-memory databases** for:
- Fast test execution
- Isolation between tests
- No external dependencies
- Easy cleanup

## Key Testing Patterns

1. **Mocking**: Unit tests use PHPUnit mocks for repository dependencies
2. **In-Memory Database**: Integration tests use SQLite in-memory databases
3. **Full Stack Testing**: Integration tests verify complete workflows from service to database
4. **Error Handling**: Comprehensive validation and error scenario testing
5. **Edge Cases**: Tests cover boundary conditions and error states

## Coverage Areas

### ✅ Well Covered
- Product management (CRUD operations)
- Cart operations (add, update, remove, clear)
- Checkout process (order creation, validation)
- Admin authentication and authorization
- Soft delete functionality
- Stock management
- Data validation

### 🔄 Areas for Potential Expansion
- API endpoint error handling edge cases
- Concurrent access scenarios
- Performance testing
- Frontend JavaScript testing
- End-to-end browser testing

## Test Quality Metrics

- **Isolation**: Each test is independent and can run in any order
- **Repeatability**: Tests produce consistent results
- **Speed**: Fast execution using in-memory databases
- **Maintainability**: Clear test structure and naming conventions
- **Coverage**: Comprehensive coverage of business logic and data access layers

## Conclusion

The QuickShop test suite provides robust coverage of the application's core functionality, ensuring reliability and correctness across all major features. The combination of unit tests (for isolated component testing) and integration tests (for full workflow validation) creates a comprehensive safety net for the codebase.


