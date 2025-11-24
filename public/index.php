<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickShop - Product Catalog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .product-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
            min-height: 60px;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
        }

        .product-stock {
            font-size: 0.9rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .stock-in {
            background: #d4edda;
            color: #155724;
        }

        .stock-low {
            background: #fff3cd;
            color: #856404;
        }

        .stock-out {
            background: #f8d7da;
            color: #721c24;
        }

        .loading {
            text-align: center;
            color: white;
            font-size: 1.5rem;
            padding: 50px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            color: white;
            padding: 60px 20px;
        }

        .empty-state h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🛍️ QuickShop</h1>
            <p class="subtitle">Your Modern Product Catalog</p>
        </header>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Loading products...</p>
        </div>

        <div id="error" class="error" style="display: none;"></div>

        <div id="products-container" class="products-grid" style="display: none;"></div>

        <div id="empty-state" class="empty-state" style="display: none;">
            <h2>No Products Found</h2>
            <p>There are no products available at the moment.</p>
        </div>
    </div>

    <script>
        const API_URL = 'api/products.php';

        async function fetchProducts() {
            try {
                const response = await fetch(API_URL);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const products = await response.json();
                displayProducts(products);
            } catch (error) {
                console.error('Error fetching products:', error);
                showError('Failed to load products. Please make sure the database is set up.');
            }
        }

        function displayProducts(products) {
            const loadingEl = document.getElementById('loading');
            const containerEl = document.getElementById('products-container');
            const errorEl = document.getElementById('error');
            const emptyStateEl = document.getElementById('empty-state');

            loadingEl.style.display = 'none';
            errorEl.style.display = 'none';

            if (!products || products.length === 0) {
                emptyStateEl.style.display = 'block';
                return;
            }

            emptyStateEl.style.display = 'none';
            containerEl.style.display = 'grid';
            containerEl.innerHTML = '';

            products.forEach(product => {
                const card = createProductCard(product);
                containerEl.appendChild(card);
            });
        }

        function createProductCard(product) {
            const card = document.createElement('div');
            card.className = 'product-card';

            const stockClass = getStockClass(product.stock);
            const stockText = getStockText(product.stock);

            card.innerHTML = `
                <div class="product-name">${escapeHtml(product.name)}</div>
                <div class="product-description">${escapeHtml(product.description || 'No description available')}</div>
                <div class="product-footer">
                    <div class="product-price">$${formatPrice(product.price)}</div>
                    <div class="product-stock ${stockClass}">${stockText}</div>
                </div>
            `;

            return card;
        }

        function getStockClass(stock) {
            if (stock === 0) return 'stock-out';
            if (stock < 10) return 'stock-low';
            return 'stock-in';
        }

        function getStockText(stock) {
            if (stock === 0) return 'Out of Stock';
            if (stock < 10) return `Only ${stock} left`;
            return `${stock} in stock`;
        }

        function formatPrice(price) {
            return parseFloat(price).toFixed(2);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function showError(message) {
            const loadingEl = document.getElementById('loading');
            const errorEl = document.getElementById('error');

            loadingEl.style.display = 'none';
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }

        // Load products on page load
        fetchProducts();
    </script>
</body>
</html>
