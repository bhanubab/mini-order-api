# Mini Order Management API

A REST API built with Laravel 12 for managing products and orders with JWT authentication.

---

## Tech Stack

- **Laravel** 12
- **PHP** 8.2
- **MySQL** 8.0
- **Redis** (caching)
- **JWT Auth** (tymon/jwt-auth)
- **Docker** + Nginx
- **PHPUnit** (feature tests)

---

## Project Structure

```
mini-order-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   └── OrderController.php
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── UpdateProductRequest.php
│   │   │   └── StoreOrderRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── ProductResource.php
│   │       ├── OrderResource.php
│   │       └── OrderItemResource.php
│   ├── Jobs/
│   │   └── ProcessOrder.php
│   ├── Mail/
│   │   └── OrderPlaced.php
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Order.php
│       └── OrderItem.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   └── nginx/
│       └── nginx.conf
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── AuthTest.php
│       ├── ProductTest.php
│       └── OrderTest.php
├── Dockerfile
├── docker-compose.yml
└── README.md
```

---

## Setup — Option A: Docker (Recommended)

### Requirements
- Docker Desktop installed and running

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/yourusername/mini-order-api.git
cd mini-order-api
```

**2. Copy environment file**
```bash
cp .env.docker .env
```

**3. Build and start all containers**
```bash
docker-compose up -d --build
```

**4. Generate app key**
```bash
docker-compose exec app php artisan key:generate
```

**5. Generate JWT secret**
```bash
docker-compose exec app php artisan jwt:secret
```

**6. Run migrations and seeders**
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

**7. API is ready at**
```
http://localhost:8000/api
```

### Docker Services

| Service | Container | Port |
|---|---|---|
| Laravel App | mini_order_app | 9000 |
| Nginx | mini_order_nginx | 8000 |
| MySQL | mini_order_mysql | 3307 |
| Redis | mini_order_redis | 6379 |
| Queue Worker | mini_order_queue | — |

### Docker Commands

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View logs
docker-compose logs app
docker-compose logs mysql

# Run artisan commands
docker-compose exec app php artisan <command>

# Enter app container
docker-compose exec app bash

# Rebuild after changes
docker-compose up -d --build
```

---

## Setup — Option B: Local XAMPP

### Requirements
- XAMPP with PHP 8.2+
- Composer
- MySQL

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/yourusername/mini-order-api.git
cd mini-order-api
```

**2. Install dependencies**
```bash
composer install
```

**3. Copy environment file**
```bash
cp .env.example .env
```

**4. Update `.env` for local**
```env
DB_HOST=127.0.0.1
DB_USERNAME=root
DB_PASSWORD=
CACHE_STORE=file
SESSION_DRIVER=file
```

**5. Generate keys**
```bash
php artisan key:generate
php artisan jwt:secret
```

**6. Run migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

**7. Start server**
```bash
php artisan serve
```

---

## Default Seeded Users

| Name | Email | Password |
|---|---|---|
| Admin User | admin@test.com | password123 |
| John Doe | john@test.com | password123 |

---

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/register` | Register new user | No |
| POST | `/api/login` | Login and get JWT token | No |
| POST | `/api/logout` | Logout user | Yes |

### Products

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/products` | List all products | Yes |
| POST | `/api/products` | Create product | Yes |
| GET | `/api/products/{id}` | Get single product | Yes |
| PUT | `/api/products/{id}` | Update product | Yes |
| DELETE | `/api/products/{id}` | Delete product | Yes |

#### Product Search & Filter Parameters

| Parameter | Type | Description | Example |
|---|---|---|---|
| `search` | string | Search by name or description | `?search=laptop` |
| `min_price` | number | Minimum price | `?min_price=100` |
| `max_price` | number | Maximum price | `?max_price=500` |
| `in_stock` | boolean | Only in-stock products | `?in_stock=1` |
| `sort_by` | string | Sort field (name/price/stock/created_at) | `?sort_by=price` |
| `sort_dir` | string | Sort direction (asc/desc) | `?sort_dir=asc` |

### Orders

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/orders` | Place new order | Yes |
| GET | `/api/orders` | Get user orders | Yes |
| GET | `/api/orders/{id}` | Get order details | Yes |

---

## Request & Response Examples

### Register
```json
POST /api/register
{
    "name": "John Doe",
    "email": "john@test.com",
    "password": "password123",
    "password_confirmation": "password123"
}

Response 201:
{
    "user": { "id": 1, "name": "John Doe", "email": "john@test.com" },
    "token": "eyJ0eXAiOiJKV1Q..."
}
```

### Login
```json
POST /api/login
{
    "email": "john@test.com",
    "password": "password123"
}

Response 200:
{
    "token": "eyJ0eXAiOiJKV1Q..."
}
```

### Create Product
```json
POST /api/products
Authorization: Bearer YOUR_TOKEN
{
    "name": "Laptop",
    "description": "Gaming laptop",
    "price": 999.99,
    "stock": 10
}

Response 201:
{
    "data": {
        "id": 1,
        "name": "Laptop",
        "description": "Gaming laptop",
        "price": "999.99",
        "stock": 10,
        "created_at": "2024-01-01 12:00:00"
    }
}
```

### Place Order
```json
POST /api/orders
Authorization: Bearer YOUR_TOKEN
{
    "items": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 2, "quantity": 1 }
    ]
}

Response 201:
{
    "data": {
        "id": 1,
        "status": "pending",
        "total_price": "2029.97",
        "items": [...],
        "created_at": "2024-01-01 12:00:00"
    }
}
```

---

## R&D Features

### 1. Redis Caching for Products
Products are cached in Redis to reduce database load.

- Product list cached with unique key based on query parameters (search, filters, sort)
- Individual products cached by ID
- Cache TTL: 1 hour
- Cache automatically cleared when a product is created, updated, or deleted
- Uses `Cache::tags(['products'])` for grouped cache invalidation

```env
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### 2. API Rate Limiting
All API routes are rate limited to prevent abuse.

- Default: 60 requests per minute per user
- Identified by user ID when authenticated, IP address when guest
- Returns `429 Too Many Requests` when limit is exceeded
- Configured via Laravel's built-in throttle middleware

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi();
})
```

### 3. Queue for Order Processing
Orders are processed asynchronously using Laravel's database queue driver.

- On order creation, a `ProcessOrder` job is dispatched immediately
- Job verifies stock availability again inside transaction
- Order status transitions: `pending → processing → completed`
- Failed jobs are retried up to 3 times automatically
- All failed jobs stored in `failed_jobs` table for manual retry

```bash
# Start queue worker
php artisan queue:work

# Inside Docker (runs automatically)
docker-compose logs queue
```

### 4. Email Notification After Order
A confirmation email is sent to the user after every successful order.

- Email sent inside `ProcessOrder` job after order is completed
- Uses Laravel Mailable class `App\Mail\OrderPlaced`
- Email includes: order ID, date, status, itemized product list, total price
- Configured with Mailtrap for development (catches emails without sending)

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### 5. Product Search Filters
Advanced filtering on the products list endpoint.

- Search by product name or description (partial match)
- Filter by minimum and maximum price range
- Filter by stock availability (in-stock only)
- Sort by name, price, stock, or created date
- Sort direction: ascending or descending

```
GET /api/products?search=laptop&min_price=500&sort_by=price&sort_dir=asc
```

---

## Running Tests

### Setup Test Database
```bash
# Docker
docker-compose exec mysql mysql -u root -prootpassword -e "CREATE DATABASE IF NOT EXISTS mini_order_api_test; GRANT ALL PRIVILEGES ON mini_order_api_test.* TO 'laravel'@'%'; FLUSH PRIVILEGES;"

# Local
# Create mini_order_api_test database in phpMyAdmin
```

### Run All Tests
```bash
# Docker
docker-compose exec app php artisan test

# Local
php artisan test
```

### Run Specific Tests
```bash
# Auth tests only
docker-compose exec app php artisan test --filter=AuthTest

# Product tests only
docker-compose exec app php artisan test --filter=ProductTest

# Order tests only
docker-compose exec app php artisan test --filter=OrderTest

# With detailed output
docker-compose exec app php artisan test --verbose
```

### Test Coverage

| Test File | Tests | What is Tested |
|---|---|---|
| AuthTest | 11 | Register, Login, Logout |
| ProductTest | 13 | CRUD, Search, Filters, Auth |
| OrderTest | 11 | Place order, Stock check, Auth |
| **Total** | **35** | **Full API coverage** |

---

## Database Schema

### users
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | User name |
| email | varchar | Unique email |
| password | varchar | Hashed password |
| created_at | timestamp | — |
| updated_at | timestamp | — |

### products
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| name | varchar | Product name |
| description | text | Product description |
| price | decimal(10,2) | Product price |
| stock | integer | Available stock |
| created_at | timestamp | — |
| updated_at | timestamp | — |

### orders
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| total_price | decimal(10,2) | Total order price |
| status | enum | pending/processing/completed/failed |
| created_at | timestamp | — |
| updated_at | timestamp | — |

### order_items
| Column | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | Foreign key → orders |
| product_id | bigint | Foreign key → products |
| quantity | integer | Ordered quantity |
| price | decimal(10,2) | Price at time of order |
| created_at | timestamp | — |
| updated_at | timestamp | — |

---

## Error Responses

| Code | Meaning |
|---|---|
| 200 | Success |
| 201 | Created |
| 401 | Unauthenticated — missing or invalid token |
| 404 | Resource not found |
| 422 | Validation error or insufficient stock |
| 429 | Too many requests — rate limit exceeded |
| 500 | Server error |

---

## Postman Collection

Import `Mini-Order-API.postman_collection.json` into Postman.

Features:
- Auto-saves JWT token after login/register
- All endpoints pre-configured
- Sample request bodies included

---

## License

MIT
