# Auth Service

A standalone authentication microservice built with Laravel, providing JWT-based API authentication for InterviewPrep and future projects.

---

## 🚀 Features
- User registration and login via JWT
- Token refresh and invalidation
- Secure password hashing
- API-first design (no Blade views)
- Ready to integrate with other services

---

## 📂 Project Setup

### Requirements
- PHP >= 8.1
- Composer
- MySQL (via XAMPP)
- Herd (for local Laravel environment)

### Installation
```bash
# Clone the repository
git clone https://github.com/irfan-farooq-dev/auth-service.git

cd auth-service

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auth_service
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Generate JWT secret
php artisan jwt:secret
