# University Registration System — Backend API

RESTful backend API for the **University Registration System**, built with **Laravel 13**, **Laravel Sanctum** (Hybrid SPA Session Cookie + Bearer Token Authentication), and **PostgreSQL**.

---

## Tech Stack

- **Framework:** Laravel 13 (`php ^8.3`)
- **Authentication:** Laravel Sanctum 4 (Supports both SPA Stateful Cookies & Bearer Tokens)
- **Database:** PostgreSQL (`pgsql`)
- **Architecture:** FormRequest Validation, Standardized JSON Exception Handling, Role-Based Access (`admin`, `staff`, `student`)

---

## Prerequisites

- PHP `>= 8.3`
- Composer
- PostgreSQL

---

## Installation & Setup

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Configure environment variables:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your PostgreSQL database credentials:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=university_registration
   DB_USERNAME=postgres
   DB_PASSWORD=your_password

   SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost:8000,127.0.0.1:8000
   SESSION_DRIVER=database
   ```

3. **Run database migrations and seed default roles (`admin`, `staff`, `student`):**
   ```bash
   php artisan migrate --seed
   ```
   *(Or run the role seeder directly: `php artisan db:seed --class=RoleSeeder`)*

4. **Start the development server:**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000/api`.

---

## Default Roles

Seeded via `database/seeders/RoleSeeder.php`:

| Role Name | Description |
| :--- | :--- |
| `admin` | System administrator |
| `staff` | University Staff |
| `student` | University Student *(assigned automatically on `/api/auth/register`)* |

---

## API Endpoints

Base URL: `http://localhost:8000/api`

| Method | Endpoint | Auth Required | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/register` | No | Register a new student account |
| `POST` | `/api/auth/login` | No | Authenticate user and issue session / Bearer token |
| `GET` | `/api/auth/user` | Yes (`auth:sanctum`) | Get the currently authenticated user and role |
| `POST` | `/api/auth/logout` | Yes (`auth:sanctum`) | Revoke current Bearer token and invalidate session |

---

### 1. Register (`POST /api/auth/register`)

**Validation Rules:**
- `name`: required, string, max 255 characters
- `email`: required, valid email, max 255 characters, unique in `users`
- `password`: required, string, min 8 characters, uppercase + lowercase (`mixedCase`), numbers, symbols, and must match `password_confirmation`

**Request Body:**
```json
{
    "name": "Student One",
    "email": "student1@example.com",
    "password": "Student@123",
    "password_confirmation": "Student@123"
}
```

**Success Response (`201 Created`):**
```json
{
    "message": "Registration successful.",
    "user": {
        "id": 1,
        "name": "Student One",
        "email": "student1@example.com",
        "role_id": 3,
        "role": {
            "id": 3,
            "name": "student",
            "description": "University Student"
        }
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}
```

---

### 2. Login (`POST /api/auth/login`)

**Request Body:**
```json
{
    "email": "student1@example.com",
    "password": "Student@123"
}
```

**Success Response (`200 OK`):**
```json
{
    "message": "Login successful.",
    "user": {
        "id": 1,
        "name": "Student One",
        "email": "student1@example.com",
        "role_id": 3,
        "role": {
            "id": 3,
            "name": "student",
            "description": "University Student"
        }
    },
    "token": "2|xYzAbCdEfGhIjKlMnOpQrStUvW..."
}
```

---

### 3. Get Current User (`GET /api/auth/user`)

**Headers (Postman / Bearer Token):**
```http
Accept: application/json
Authorization: Bearer <your_token>
```

**Success Response (`200 OK`):**
```json
{
    "message": "User retrieved successfully.",
    "user": {
        "id": 1,
        "name": "Student One",
        "email": "student1@example.com",
        "role_id": 3,
        "role": {
            "id": 3,
            "name": "student",
            "description": "University Student"
        }
    }
}
```

---

### 4. Logout (`POST /api/auth/logout`)

**Headers (Postman / Bearer Token):**
```http
Accept: application/json
Authorization: Bearer <your_token>
```

**Success Response (`200 OK`):**
```json
{
    "message": "Logout successful."
}
```

---

## Error Responses

All `/api/*` endpoints return standardized JSON error responses:

- **`422 Unprocessable Entity` (Validation Error):**
  ```json
  {
      "message": "This email address is already registered.",
      "errors": {
          "email": [
              "This email address is already registered."
          ]
      }
  }
  ```
- **`401 Unauthorized`:**
  ```json
  {
      "message": "Unauthenticated. Please log in first."
  }
  ```
- **`403 Forbidden`:**
  ```json
  {
      "message": "You are not authorized to perform this action."
  }
  ```
- **`404 Not Found`:**
  ```json
  {
      "message": "The requested resource or endpoint was not found."
  }
  ```
- **`405 Method Not Allowed`:**
  ```json
  {
      "message": "HTTP method not allowed for this endpoint."
  }
  ```
- **`500 Internal Server Error`:**
  ```json
  {
      "message": "An unexpected server error occurred."
  }
  ```
