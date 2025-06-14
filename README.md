# Blog Genzet Backend

This is the backend API for **Blog Genzet**, built with **Laravel 12** and designed to support a blogging platform with user authentication, article management, category filtering, and JWT-based security.

## 🔧 Tech Stack

- Laravel 12 (PHP 8.2)
- JWT Auth (`tymon/jwt-auth`)
- Swagger Documentation (via `l5-swagger`)
- PostgreSQL or MySQL
- Laravel Form Request Validation
- RESTful API

## 📦 Features

- ✅ User registration & login with JWT
- ✅ Role-based access (`Admin`, `User`)
- ✅ CRUD for Articles
- ✅ CRUD for Categories
- ✅ Slug-based article fetching
- ✅ Swagger-based API documentation
- ✅ Error handling & response formatter
- ✅ File upload for thumbnails

## 📄 API Documentation

After running the server:

```bash
php artisan serve
```
You can access the API documentation at:

```
http://localhost:8000/api/documentation
```
## 🚀 Getting Started
### Prerequisites
- PHP 8.2 or higher
- Composer
- PostgreSQL or MySQL database
- Node.js (for frontend, if applicable)
### Installation
1. Clone the repository:
```bash 
git clone https://github.com/your-username/blog-genzet-backend.git
cd blog-genzet-backend
```
2. Install dependencies:
```bash
composer install
```
3. Copy the example environment file:
```bash
cp .env.example .env
php artisan key:generate
```
4. Configure database credentials in .env.
5. Run migrations and seed the database:
```bash
php artisan migrate
```

6. Generate JWT secret key:
```bash
php artisan jwt:secret
```

7. Start the server:
```bash
php artisan serve
```

## 🔐 Authentication
This project uses JWT for authentication. After login/register, use the returned token in requests:
```bash
Authorization: Bearer <your-token-here>
```

### Frontend Repository

The frontend repository is available here: [Blog Genzet Frontend](https://github.com/wikukarno/blog-genzet-frontend)


Happy coding! 👋🏻