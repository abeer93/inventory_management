# Inventory Management API

Project Brief
    Purpose:
        This project is a simplified RESTful API for managing inventory across multiple warehouses. It allows CRUD operations on warehouses, inventory items, stock, and stock transfers.

### Key Features:
1. Manage warehouses and inventory items.
2. Track stock per warehouse.
3. Transfer stock between warehouses with validation.
4. Paginated inventory listings with filters (item_name, min_price, max_price, per_page).
5. Low stock detection event.
6. Authentication via Laravel Sanctum.
7. Input validation to prevent invalid data and SQL injection.


## Requirement

1. [Laravel 10.x](https://laravel.com/docs/12.x)
2. [PHP >= 8.1](http://php.net/downloads.php)
3. [Composer](https://getcomposer.org/)


## Installation
1. Clone the repo via this url
  ```
    git clone https://github.com/abeer93/inventory_management.git
  ```
2. Enter inside the folder
  ```
    cd inventory_management
  ```
3. Create a `.env` file by running the following command
  ```
    cp .env.example .env
  ```
4. Install various packages and dependencies:
  ```
    composer install
  ```
5. Generate an encryption key for the app:
  ```
    php artisan key:generate
  ```
6. Run migartions
  ```
    php artisan migrate --seed
  ```
7. Run test cases
    - create .env.testing file
    - Update .env.testing
        ```
        DB_CONNECTION=mysql
        DB_DATABASE=
        DB_USERNAME=
        DB_PASSWORD=
        ```
    - run test
        ```
            php artisan optimize:clear
            php artisan test
        ```
8. Run Servers
  ```
    php artisan serve --port 8080
  ```

## Important Environment variables (dev)

| Name | Type | Default | Description |
|------|------|---------|-------------|
| `APP_KEY` | `string` | `SomeRandomStringWith32Characters` | Application key |
| `DB_CONNECTION` | `string` | `mysql` | DB connection to use |
| `DB_HOST` | `string` | `mysql` | Hostname to connect |
| `DB_DATABASE` | `string` | `fleet_management` | Database name |
| `DB_USERNAME` | `string` | `root` | Database username |
| `DB_PASSWORD` | `string` | `empty` | Database password |


## Postman Collection
The Postman collection is provided in **[postman_collection.json](./inventory_management.postman_collection.json)**.
It contains preconfigured requests for:
1. /api/inventory
2. /api/warehouses/{id}/inventory
3. /api/stock-transfers
4. /api/login

### Test Credentials
- Email: admin@test.com 
- Password: password

Import it into Postman to test APIs locally.


## 📁 Folder Structure

- app/
    - ├─ Http/
        - │   ├─ Controllers/         # API controllers
        - │   ├─ Requests/            # FormRequest validation classes
        - │   └─ Resources/           # API Resources
    - ├─ Models/                  # Eloquent models
    - ├─ Services/                # Business logic classes (e.g., StockTransferService)
    - ├─ Events/                  # Event classes (LowStockDetected)
    - └─ Listeners/               # Event listeners

- database/
    - ├─ factories/               # factories files
    - ├─ migrations/              # Database migration files
    - ├─ seeders/                 # Seeders for testing and initial data

- tests/
    - ├─ Feature/                 # Feature tests for API endpoints
    - └─ Unit/                    # Unit tests for services/business logic

