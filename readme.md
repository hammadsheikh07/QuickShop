# QuickShop - E-commerce Store

A modern e-commerce web application built using PHP with a clean architecture, featuring product catalog display and RESTful API.

## Features
- ✅ Product listing with modern UI
- ✅ RESTful API for products (GET, POST, PUT, DELETE)
- ✅ Product repository and service layer
- ✅ Unit tests with PHPUnit
- 🔄 Shopping cart (planned)
- 🔄 Checkout flow (planned)
- 🔄 Admin product management (planned)
- 🔄 CI/CD with GitHub Actions (planned)

## Tech Stack
- PHP 8+
- SQLite (can be switched to MySQL)
- Apache/Nginx
- PHPUnit
- Composer

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
- Create a SQLite database at `data/quickshop.db`
- Create the `products` table
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
- **Frontend**: `http://localhost/QuickShop/public/` (or your configured URL)
- **API Endpoint**: `http://localhost/QuickShop/public/api/products.php`

## API Endpoints

### Get All Products
```
GET /api/products.php
```

### Get Product by ID
```
GET /api/products.php/{id}
```

### Create Product
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

### Update Product
```
PUT /api/products.php/{id}
Content-Type: application/json

{
  "name": "Updated Name",
  "price": 89.99,
  "stock": 5
}
```

### Delete Product
```
DELETE /api/products.php/{id}
```

## Running Tests
```bash
vendor/bin/phpunit tests/unit
```

## Project Structure
```
QuickShop/
├── public/              # Web-accessible files
│   ├── index.php       # Frontend HTML page
│   └── api/
│       └── products.php # API endpoint
├── src/
│   ├── Config/         # Configuration (Database)
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Domain models
│   ├── Repositories/   # Data access layer
│   └── Services/       # Business logic
├── tests/              # Unit tests
├── scripts/            # Setup scripts
└── data/               # Database files (gitignored)
```

## Switching to MySQL

To use MySQL instead of SQLite, edit `src/config/database.php` and uncomment the MySQL connection code, then update the connection parameters.