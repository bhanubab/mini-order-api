# Mini Order Management API

A RESTful API built with **Laravel 12**, **PHP 8.2**, and **MySQL** for managing products and orders with JWT authentication, queue processing, email notifications, and Redis caching.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Setup](#environment-setup)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [API Endpoints](#api-endpoints)
- [R&D Features](#rd-features)
- [Project Structure](#project-structure)
- [Postman Collection](#postman-collection)

---

## Tech Stack

| Technology | Version | Purpose |
|---|---|---|
| Laravel | 12.x | PHP Framework |
| PHP | 8.2 | Server-side language |
| MySQL | 8.0+ | Relational database |
| JWT Auth | tymon/jwt-auth | API authentication |
| Redis | 7.x | Caching layer |
| Mailtrap | — | Email testing (development) |

---

## Features

### Core Features
- **JWT Authentication** — Register, login, logout with token-based auth
- **Product Management** — Full CRUD with search and filters
- **Order System** — Create orders with stock validation and total calculation
- **Business Logic** — Stock check, stock reduction, order items saved in DB transaction

### R&D Features
- **Redis Caching** — Product list and single products cached for 1 hour
- **API Rate Limiting** — 60 requests per minute per user/IP
- **Queue Processing** — Orders processed asynchronously via database queue
- **Email Notifications** — Order confirmation email sent after successful order
- **Product Search Filters** — Filter by name, price range, stock, with sorting

---

## Requirements

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Redis (for caching)
- Node.js (optional, for assets)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/YOURUSERNAME/mini-order-api.git
cd mini-order-api
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Copy environment file

```bash
cp .env.example .env
```

### 4. Generate application key

```bash
php artisan key:generate
```

### 5. Generate JWT secret

```bash
php artisan jwt:secret
```

---

## Environment Setup

Open `.env` and configure the following:

```env
APP_NAME="Mini Order API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_order_api
DB_USERNAME=root
DB_PASSWORD=

# Cache (Redis)
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=database

# Mail (Mailtrap for development)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@miniorderapi.com
MAIL_FROM_NAME="Mini Order API"
```

> Get free Mailtrap credentials at [mailtrap.io](https://mailtrap.io)

---

## Database Setup

### Run migrations and seed sample data

```bash
php artisan migrate --seed
```

This creates all tables and seeds:

| Table | Seeded rows |
|---|---|
| users | 2 (admin@test.com, john@test.com) |
| products | 5 (Laptop, Mouse, Keyboard, Monitor, Headphones) |
| orders | 0 |
| order_items | 0 |
| jobs | 0 |

### Default login credentials

```
Email:    admin@test.com
Password: password123
```

### Reset database

```bash
php artisan migrate:fresh --seed
```

---

## Running the Application

### 1. Start the development server

```bash
php artisan serve
```

API available at: `http://localhost:8000`

### 2. Start the queue worker (second terminal)

```bash
php artisan queue:work
```

> Keep this running to process orders and send emails

### 3. Start Redis (Windows)

```bash
redis-server
```

---

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/register` | Register new user | No |
| POST | `/api/login` | Login and get JWT token | No |
| POST | `/api/logout` | Logout and invalidate token | Yes |

#### Register
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "secret123"
}
```

Response:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGci..."
}
```

> Use this token in all protected requests as: `Authorization: Bearer {token}`

---

### Products

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| GET | `/api/products` | List all products (with filters) | Yes |
| POST | `/api/products` | Create new product | Yes |
| GET | `/api/products/{id}` | Get single product | Yes |
| PUT | `/api/products/{id}` | Update product | Yes |
| DELETE | `/api/products/{id}` | Delete product | Yes |

#### Create Product
```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Laptop",
  "description": "Gaming laptop 16GB RAM",
  "price": 999.99,
  "stock": 10
}
```

#### List Products with Filters
```http
GET /api/products?search=laptop&min_price=100&max_price=1000&in_stock=1&sort_by=price&sort_dir=asc
Authorization: Bearer {token}
```

Available query parameters:

| Parameter | Type | Description | Example |
|---|---|---|---|
| `search` | string | Search by name or description | `?search=laptop` |
| `min_price` | number | Minimum price filter | `?min_price=50` |
| `max_price` | number | Maximum price filter | `?max_price=500` |
| `in_stock` | boolean | Only show in-stock items | `?in_stock=1` |
| `sort_by` | string | Sort field (name, price, stock, created_at) | `?sort_by=price` |
| `sort_dir` | string | Sort direction (asc, desc) | `?sort_dir=asc` |

---

### Orders

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| POST | `/api/orders` | Place a new order | Yes |
| GET | `/api/orders` | Get all orders for logged-in user | Yes |
| GET | `/api/orders/{id}` | Get single order details | Yes |

#### Place an Order
```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

Response:
```json
{
  "data": {
    "id": 1,
    "status": "pending",
    "total_price": "2079.97",
    "items": [
      {
        "product": { "id": 1, "name": "Laptop", "price": "999.99" },
        "quantity": 2,
        "price": "999.99",
        "subtotal": "1999.98"
      }
    ],
    "created_at": "2024-01-01 12:00:00"
  }
}
```

---

### Sample Error Responses

#### Validation Error (422)
```json
{
  "error": "Validation failed",
  "details": {
    "email": ["The email field is required."]
  }
}
```

#### Unauthenticated (401)
```json
{
  "error": "Unauthenticated"
}
```

#### Not Found (404)
```json
{
  "error": "Resource not found"
}
```

#### Insufficient Stock (422)
```json
{
  "message": "Insufficient stock for: Laptop"
}
```

---

## R&D Features

### 1. Redis Caching for Products

Products are cached in Redis to reduce database load and improve response time.

**Implementation:**
- Product list responses are cached using a key based on `md5` hash of query parameters
- Individual product responses cached by product ID
- Cache TTL is **1 hour** (3600 seconds)
- Cache uses `Cache::tags(['products'])` for grouped invalidation
- Cache is automatically flushed when any product is created, updated, or deleted

**Configuration:**
```env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

**Result:** Repeated identical requests are served from Redis without hitting MySQL.

---

### 2. API Rate Limiting

Protects the API from abuse by limiting request frequency.

**Implementation:**
- Applied globally to all `/api/*` routes via Laravel's built-in throttle middleware
- Limit: **60 requests per minute** per authenticated user or IP address
- Configured in `bootstrap/app.php` using `$middleware->throttleApi()`

**Response when limit exceeded (429):**
```json
{
  "message": "Too Many Attempts."
}
```

---

### 3. Queue for Order Processing

Orders are processed asynchronously to keep API responses fast.

**Implementation:**
- On order creation, a `ProcessOrder` job is dispatched to the database queue
- The job verifies stock availability, updates order status through lifecycle stages
- Order status progression: `pending → processing → completed`
- Failed jobs are retried up to **3 times** automatically
- All failed jobs are stored in the `failed_jobs` table for manual retry

**Run the worker:**
```bash
php artisan queue:work
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

---

### 4. Email Notification After Order

A confirmation email is sent to the user after every successful order.

**Implementation:**
- Email is sent inside the `ProcessOrder` job after order status changes to `completed`
- Uses Laravel `Mailable` class: `App\Mail\OrderPlaced`
- Email template: `resources/views/emails/order-placed.blade.php`
- Includes order ID, date, status, itemized product list, and total price
- Uses Mailtrap SMTP for development email testing

**Email contains:**
- Order confirmation number
- Order date and status
- Full itemized product table with subtotals
- Grand total

---

### 5. Product Search Filters

The products list endpoint supports flexible filtering and sorting.

**Implementation:**
- `search` — full-text search on product name and description using SQL `LIKE`
- `min_price` / `max_price` — price range filtering
- `in_stock` — filters to only show products with `stock > 0`
- `sort_by` — sort by `name`, `price`, `stock`, or `created_at`
- `sort_dir` — ascending or descending direction
- Whitelist validation prevents SQL injection via sort fields

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
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       └── ProductSeeder.php
├── resources/
│   └── views/
│       └── emails/
│           └── order-placed.blade.php
├── routes/
│   └── api.php
├── bootstrap/
│   └── app.php
├── .env.example
└── README.md
```

---

## Postman Collection

A complete Postman collection is included in the repository: `Mini-Order-API.postman_collection.json`

**Import steps:**
1. Open Postman
2. Click **Import**
3. Select `Mini-Order-API.postman_collection.json`
4. All endpoints load with pre-configured headers

**The collection includes auto token saving** — after login or register, the JWT token is automatically saved and used in all subsequent requests.

---

## Database Schema

```
users
├── id (PK)
├── name
├── email (unique)
├── password
└── timestamps

products
├── id (PK)
├── name
├── description
├── price
├── stock
└── timestamps

orders
├── id (PK)
├── user_id (FK → users.id)
├── total_price
├── status (pending/processing/completed/failed)
└── timestamps

order_items
├── id (PK)
├── order_id (FK → orders.id)
├── product_id (FK → products.id)
├── quantity
├── price
└── timestamps
```

---

## Quick Commands Reference

```bash
# Install
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate --seed

# Run
php artisan serve
php artisan queue:work

# Maintenance
php artisan migrate:fresh --seed
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan queue:retry all
```
