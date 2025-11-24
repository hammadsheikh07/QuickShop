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
- ✅ Admin module with product management
- ✅ CI/CD with GitHub Actions (planned)

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
  - `products` - Product catalog (with soft delete support via `deleted_at` column)
  - `cart_items` - Shopping cart items (session-based)
  - `orders` - Customer orders
  - `order_items` - Order line items
  - `admins` - Admin user accounts
- Insert 8 sample products
- Create default admin user (username: `admin`, password: `admin123`)

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