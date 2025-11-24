# Docker Setup for QuickShop

This guide will help you run QuickShop using Docker without any local setup hassle.

## Prerequisites

- Docker (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)

## Quick Start

1. **Clone or navigate to the project directory**
   ```bash
   cd QuickShop
   ```

2. **Start the application**
   ```bash
   docker-compose up -d
   ```

3. **Access the application**
   - Web application: http://localhost:8080
   - Admin panel: http://localhost:8080/admin/login.php
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`

4. **Stop the application**
   ```bash
   docker-compose down
   ```

## What's Included

The Docker setup includes:

- **Web Server**: PHP 8.2 with Apache
- **Database**: MySQL 8.0
- **Automatic Setup**: Database tables and sample data are created automatically on first run

## Services

### Web Service (Port 8080)
- PHP 8.2 with Apache
- All PHP extensions required for the application
- Composer dependencies pre-installed
- Document root: `/public`

### Database Service (Port 3306)
- MySQL 8.0
- Database: `quickshop`
- Root password: `rootpassword` (configurable via environment variables)

## Environment Variables

You can customize the database connection by creating a `.env` file or modifying `docker-compose.yml`:

```yaml
environment:
  - DB_HOST=db
  - DB_NAME=quickshop
  - DB_USER=root
  - DB_PASSWORD=rootpassword
```

## Running Tests

To run PHPUnit tests inside the Docker container:

```bash
docker-compose exec web vendor/bin/phpunit
```

Or run specific test suites:

```bash
# Unit tests only
docker-compose exec web vendor/bin/phpunit tests/unit

# Integration tests only
docker-compose exec web vendor/bin/phpunit tests/integration
```

## Troubleshooting

### Buildx Version Error

If you encounter the error "compose build requires buildx 0.17 or later", the Dockerfile has been updated to avoid this issue. However, if you still encounter it:

**Option 1: Use docker-compose up directly (Recommended)**
Instead of building separately, just run:
```bash
docker-compose up -d
```
This will automatically build the image if needed.

**Option 2: Disable BuildKit**
```bash
# Windows PowerShell
$env:DOCKER_BUILDKIT=0; docker-compose build

# Windows CMD
set DOCKER_BUILDKIT=0 && docker-compose build

# Linux/Mac
DOCKER_BUILDKIT=0 docker-compose build
```

**Option 3: Use legacy Docker builder**
```bash
docker build -t quickshop-web .
docker-compose up -d
```

### Database Connection Issues

If you encounter database connection errors:

1. Check if the database container is running:
   ```bash
   docker-compose ps
   ```

2. Check database logs:
   ```bash
   docker-compose logs db
   ```

3. Restart the services:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

### Reset Database

To reset the database and start fresh:

```bash
docker-compose down -v
docker-compose up -d
```

This will remove all volumes and recreate the database from scratch.

### View Logs

View logs from all services:
```bash
docker-compose logs -f
```

View logs from a specific service:
```bash
docker-compose logs -f web
docker-compose logs -f db
```

## Development Mode

For development, the application files are mounted as volumes, so changes to PHP files will be reflected immediately without rebuilding the container.

To rebuild the Docker image after changing dependencies:
```bash
docker-compose build
docker-compose up -d
```

## Production Considerations

For production deployment, consider:

1. Change default passwords
2. Use environment variables for sensitive data
3. Set up proper SSL/TLS certificates
4. Configure proper file permissions
5. Use a production-ready web server configuration
6. Set up database backups

