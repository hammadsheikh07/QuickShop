#!/bin/bash
set -e

echo "Waiting for database to be ready..."

DB_HOST=${DB_HOST:-db}
DB_USER=${DB_USER:-root}
DB_PASSWORD=${DB_PASSWORD:-rootpassword}

until php -r "
\$host = '${DB_HOST}';
\$user = '${DB_USER}';
\$pass = '${DB_PASSWORD}';
try {
    \$pdo = new PDO('mysql:host=' . \$host . ';charset=utf8mb4', \$user, \$pass);
    echo 'Database is ready' . PHP_EOL;
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
"; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Running database setup..."
php /var/www/html/scripts/setup_database.php

echo "Starting Apache..."
exec apache2-foreground

