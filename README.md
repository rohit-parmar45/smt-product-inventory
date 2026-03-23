# Product & Inventory Management System

A full-stack Product & Inventory Management System built with **Laravel 12**, **JWT Authentication**

---
### Prerequisites
- **Docker** & **Docker Compose**

### Setup

```bash
# Clone the repository
git clone <repo-url>
cd product_inventory_system

# Start all services (builds, migrates, seeds automatically)
docker-compose up --build -d
```

The application will be available at **http://localhost:8080**

### Demo Credentials

| Role  | Email                 | Password   |
|-------|-----------------------|------------|
| Admin | admin@inventory.com   | password   |
| User  | user@inventory.com    | password   |

---

## Architecture

### Tech Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Backend   | Laravel 12 (PHP 8.2)                |
| Auth      | JWT (php-open-source-saver/jwt-auth)|
| Database  | MySQL 8.0                           |
| Cache     | Redis 7                             |
| Web Server| Nginx (Alpine)                      |
| Frontend  | Blade + Tailwind CSS v4 + Vanilla JS|
| Build     | Vite 7                              |
| Container | Docker + Docker Compose             |

### Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php      # JWT login/logout/me
│   │       └── ProductController.php   # CRUD + filtering/sorting
│   ├── Middleware/
│   │   └── AdminMiddleware.php         # Role-based access gating
│   └── Requests/
│       ├── StoreProductRequest.php     # Create validation
│       └── UpdateProductRequest.php    # Update validation
├── Models/
│   ├── Product.php                     # Scopes, stock_status accessor
│   └── User.php                       # JWTSubject, role helper
└── Services/
    └── ProductService.php              # Business logic layer

resources/
├── css/app.css                         # Design system (dark-mode, glassmorphism)
├── js/
│   ├── app.js                          # Vite entry point
│   └── services/api.js                 # API service layer (fetch + JWT)
└── views/
    ├── layouts/app.blade.php           # Base layout with navbar
    ├── auth/login.blade.php            # Login page
    └── products/index.blade.php        # Dashboard (table, filters, modals)
```

### Design Decisions

1. **JWT over Sanctum**: Stateless authentication per project requirements. Tokens include `role` in custom claims for lightweight client-side role checks.

2. **Dynamic stock_status**: Derived via Eloquent accessor (not stored in DB) — ensures data integrity; `stock > 10 → in-stock`, `1-10 → low-stock`, `0 → out-of-stock`.

3. **Service Layer Pattern**: `ProductService` encapsulates business logic, keeping controllers thin and testable.

4. **Query Scopes**: Filtering, searching, and sorting logic lives in the `Product` model as reusable scopes — composable and DRY.

5. **Blade + Vanilla JS**: Avoids SPA framework overhead while still delivering a dynamic, interactive UI with modals, debounced search, and live filtering.

6. **Global API Module**: `window.InventoryAPI` pattern exposes a centralized HTTP client with automatic JWT injection and 401 handling.

---

## API Reference

### Authentication

| Method | Endpoint           | Auth | Description        |
|--------|-------------------|------|--------------------|
| POST   | `/api/auth/login`  | No   | Get JWT token      |
| POST   | `/api/auth/logout` | JWT  | Invalidate token   |
| GET    | `/api/auth/me`     | JWT  | Get current user   |

### Products

| Method | Endpoint                    | Auth       | Description           |
|--------|----------------------------|------------|-----------------------|
| GET    | `/api/products`             | JWT        | List products (paginated) |
| GET    | `/api/products/categories`  | JWT        | Get distinct categories   |
| POST   | `/api/products`             | JWT+Admin  | Create product        |
| PUT    | `/api/products/{id}`        | JWT+Admin  | Update product        |
| DELETE | `/api/products/{id}`        | JWT+Admin  | Delete product        |

### Query Parameters (GET /api/products)

| Param         | Type   | Description                              |
|---------------|--------|------------------------------------------|
| `search`      | string | Search by product name                   |
| `category`    | string | Filter by category                       |
| `stock_status`| string | `in-stock`, `low-stock`, `out-of-stock`  |
| `sort_by`     | string | `price`, `created_at`, `name`, `stock_quantity` |
| `sort_dir`    | string | `asc` or `desc`                          |
| `page`        | int    | Page number                              |
| `per_page`    | int    | Items per page (max 100)                 |

---

## Trade-offs & Assumptions

- **No SPA framework**: Chose Blade + vanilla JS to keep the stack lean. Trade-off: less component reusability compared to React/Vue, but faster initial load and simpler build.
- **JWT in localStorage**: Simple but susceptible to XSS. For production, consider httpOnly cookies with CSRF protection.
- **No rate limiting**: Authentication endpoints lack rate limiting. In production, add `throttle` middleware.
- **Seeder runs once**: The entrypoint conditionally seeds only if the users table is empty (idempotent).
- **Category as string**: No separate categories table. Acceptable for this scope; a normalized table would be better at scale.
- **No image uploads**: Products have no images. Would require file storage or S3 in a real-world scenario.

---

## Docker Services

| Service    | Container         | Port |
|------------|-------------------|------|
| PHP-FPM    | inventory_app     | 9000 |
| Nginx      | inventory_webserver | 8080 |
| MySQL 8    | inventory_db      | 3306 |
| Redis 7    | inventory_redis   | 6379 |

---

## Running Tests

```bash
docker exec -it inventory_app php artisan test
```

---

##  License

MIT
