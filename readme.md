# QuickShop - E-commerce Store

A modern e-commerce web application built using PHP with a clean architecture, featuring product catalog, shopping cart, and checkout functionality with RESTful APIs.

## Features
- ✅ Product listing with modern UI
- ✅ Product detail pages
- ✅ RESTful API for products (GET, POST, PUT, DELETE)
- ✅ Shopping cart with session-based persistence
- ✅ Cart management API (add, update, remove items)
- ✅ Checkout flow with order creation
- ✅ Order confirmation page
- ✅ Product repository and service layer
- ✅ Cart and checkout repositories and services
- ✅ Comprehensive unit and integration tests with PHPUnit
- 🔄 Admin product management (planned)
- 🔄 CI/CD with GitHub Actions (planned)

## Tech Stack
- PHP 8+
- MySQL (via XAMPP)
- Apache/Nginx
- PHPUnit for testing
- Composer for dependency management
- Vanilla JavaScript (ES6+) for frontend interactions

## Setup Instructions

### 1. Install Dependencies
```bash
composer install
```

### 2. Set Up Database
Run the database setup script to create the database and seed sample products:
```bash
php scripts/setup_database.php
```

This will:
- Create a MySQL database named `quickshop`
- Create the following tables:
  - `products` - Product catalog
  - `cart_items` - Shopping cart items (session-based)
  - `orders` - Customer orders
  - `order_items` - Order line items
- Insert 8 sample products

### 3. Configure Web Server

#### Using XAMPP (Windows)
1. Place the project in `C:\xampp\htdocs\QuickShop`
2. Access via: `http://localhost/QuickShop/public/`

#### Using PHP Built-in Server
```bash
cd public
php -S localhost:8000
```
Then visit: `http://localhost:8000`

### 4. View the Application
- **Home Page**: `http://localhost/QuickShop/public/` - Browse products
- **Product Detail**: `http://localhost/QuickShop/public/product.php?id={id}` - View product details
- **Shopping Cart**: `http://localhost/QuickShop/public/cart.php` - Manage cart items
- **Checkout**: `http://localhost/QuickShop/public/checkout.php` - Complete purchase
- **API Endpoints**: 
  - Products: `http://localhost/QuickShop/public/api/products.php`
  - Cart: `http://localhost/QuickShop/public/api/cart.php`
  - Checkout: `http://localhost/QuickShop/public/api/checkout.php`

## API Endpoints

### Products API

#### Get All Products
```
GET /api/products.php
```

#### Get Product by ID
```
GET /api/products.php/{id}
```

#### Create Product
```
POST /api/products.php
Content-Type: application/json

{
  "name": "Product Name",
  "description": "Product Description",
  "price": 99.99,
  "stock": 10
}
```

#### Update Product
```
PUT /api/products.php/{id}
Content-Type: application/json

{
  "name": "Updated Name",
  "price": 89.99,
  "stock": 5
}
```

#### Delete Product
```
DELETE /api/products.php/{id}
```

### Cart API

#### Get Cart
```
GET /api/cart.php
```
Returns cart items, total, and count for the current session.

#### Add Item to Cart
```
POST /api/cart.php
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

#### Update Cart Item Quantity
```
PUT /api/cart.php/{cartItemId}
Content-Type: application/json

{
  "quantity": 3
}
```

#### Remove Item from Cart
```
DELETE /api/cart.php/{cartItemId}
```

#### Clear Cart
```
DELETE /api/cart.php
```

### Checkout API

#### Create Order
```
POST /api/checkout.php
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "address": "123 Main St, City, State, ZIP"
}
```

#### Get Order by ID
```
GET /api/checkout.php/{orderId}
```

#### Get Orders by Session
```
GET /api/checkout.php
```
Returns all orders for the current session.

## Running Tests

### Run All Tests
```bash
vendor/bin/phpunit
```

### Run Unit Tests Only
```bash
vendor/bin/phpunit tests/unit
```

### Run Integration Tests Only
```bash
vendor/bin/phpunit tests/integration
```

### Test Coverage
The test suite includes:
- **Unit Tests**: Models, Repositories, Services (with mocks)
- **Integration Tests**: Full stack tests (Repository → Service → Controller)
- **Test Files**:
  - `ProductTest.php`, `CartItemTest.php`, `OrderTest.php` - Model tests
  - `ProductRepositoryTest.php`, `CartRepositoryTest.php`, `OrderRepositoryTest.php` - Repository tests
  - `ProductServiceTest.php`, `CartServiceTest.php`, `CheckoutServiceTest.php` - Service tests
  - `ProductIntegrationTest.php`, `CartIntegrationTest.php`, `CheckoutIntegrationTest.php` - Integration tests

## Project Structure
```
QuickShop/
├── public/                    # Web-accessible files
│   ├── index.php             # Product listing page
│   ├── product.php           # Product detail page
│   ├── cart.php              # Shopping cart page
│   ├── checkout.php          # Checkout page
│   ├── order-confirmation.php # Order confirmation page
│   ├── api/                  # API endpoints
│   │   ├── products.php      # Products API
│   │   ├── cart.php          # Cart API
│   │   └── checkout.php      # Checkout API
│   ├── components/           # Reusable components
│   │   └── navbar.php       # Navigation bar
│   ├── css/                  # Stylesheets
│   │   └── style.css        # Main stylesheet
│   ├── js/                   # JavaScript files
│   │   ├── api-config.js    # Shared API configuration
│   │   ├── notifications.js # Notification system
│   │   ├── navbar.js        # Navbar functionality
│   │   ├── cart.js          # Cart page functionality
│   │   ├── checkout.js      # Checkout functionality
│   │   └── product-actions.js # Product page actions
│   └── includes/             # Helper files
│       └── helpers.php       # PHP helper functions
├── src/
│   ├── Config/              # Configuration
│   │   └── database.php     # Database connection
│   ├── Controllers/         # Request handlers
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   └── CheckoutController.php
│   ├── Models/              # Domain models
│   │   ├── Product.php
│   │   ├── CartItem.php
│   │   └── Order.php
│   ├── Repositories/        # Data access layer
│   │   ├── ProductRepository.php
│   │   ├── CartRepository.php
│   │   └── OrderRepository.php
│   └── Services/             # Business logic
│       ├── ProductService.php
│       ├── CartService.php
│       └── CheckoutService.php
├── tests/                    # Test suite
│   ├── unit/                # Unit tests
│   │   ├── ProductTest.php
│   │   ├── CartItemTest.php
│   │   ├── OrderTest.php
│   │   ├── ProductRepositoryTest.php
│   │   ├── CartRepositoryTest.php
│   │   ├── OrderRepositoryTest.php
│   │   ├── ProductServiceTest.php
│   │   ├── CartServiceTest.php
│   │   └── CheckoutServiceTest.php
│   └── integration/         # Integration tests
│       ├── ProductIntegrationTest.php
│       ├── CartIntegrationTest.php
│       └── CheckoutIntegrationTest.php
├── scripts/                 # Setup scripts
│   └── setup_database.php   # Database initialization
└── data/                    # Database files (gitignored)
    └── quickshop.db         # SQLite database (if used)
```

## Architecture

The application follows a **layered architecture** pattern:

1. **Models** - Domain entities (Product, CartItem, Order)
2. **Repositories** - Data access layer (database operations)
3. **Services** - Business logic and validation
4. **Controllers** - Request handling and response formatting
5. **API Endpoints** - Entry points for API requests
6. **Frontend Pages** - Server-side rendered PHP pages

This architecture ensures:
- Separation of concerns
- Testability (each layer can be tested independently)
- Maintainability (changes are isolated to specific layers)
- Reusability (services and repositories can be reused)