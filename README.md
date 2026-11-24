# University Registration System — Backend API

RESTful backend API (`v1`) for the **University Registration System**, built with **Laravel 13**, **Laravel Sanctum** (Hybrid SPA Session Cookie + Bearer Token Authentication), **Laravel Socialite** (Google OAuth 2.0), and **PostgreSQL**.

---

## Tech Stack

- **Framework:** Laravel 13 (`php ^8.3`)
- **API Prefix:** `/api/v1` (configured in `bootstrap/app.php`)
- **Authentication:**
  - Laravel Sanctum 4 (Hybrid SPA Stateful Cookies & Bearer Tokens)
  - Laravel Socialite 5 (Google OAuth 2.0 Sign-In)
- **Database:** PostgreSQL (`pgsql`)
- **Architecture:** FormRequest Validation (with strict password rules), Standardized JSON Exception Handling, Role-Based Access (`admin`, `staff`, `student`)

---

## Prerequisites

- PHP `>= 8.3`
- Composer
- PostgreSQL
- Google Cloud OAuth 2.0 Client Credentials (for Google Sign-In)

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
   Update `.env` with your PostgreSQL database, Sanctum, and Google OAuth credentials:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=university_registration
   DB_USERNAME=postgres
   DB_PASSWORD=your_password

   SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost:8000,127.0.0.1:8000
   SESSION_DRIVER=database

   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/api/v1/auth/google/callback
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
   The API will be available at `http://localhost:8000/api/v1`.

---

## Default Roles

Seeded via `database/seeders/RoleSeeder.php`:

| Role Name | Description |
| :--- | :--- |
| `admin` | System administrator |
| `staff` | University Staff |
| `student` | University Student *(assigned automatically on registration and Google OAuth sign-up)* |

---

## API Endpoints (`v1`)

Base URL: `http://localhost:8000/api/v1`

| Method | Endpoint | Auth Required | Description |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | No | Register a new student account |
| `POST` | `/api/v1/auth/login` | No | Authenticate user and issue session + Bearer token |
| `GET` | `/api/v1/auth/google` | No | Get Google OAuth 2.0 authorization URL |
| `GET` | `/api/v1/auth/google/callback` | No | Handle Google OAuth callback, create/link user, and issue token |
| `GET` | `/api/v1/auth/user` | Yes (`auth:sanctum`) | Get the currently authenticated user and role |
| `POST` | `/api/v1/auth/logout` | Yes (`auth:sanctum`) | Revoke current Bearer token and invalidate session |

---

### 1. Register (`POST /api/v1/auth/register`)

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

### 2. Login (`POST /api/v1/auth/login`)

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

### 3. Google OAuth Redirect (`GET /api/v1/auth/google`)

Returns the Google OAuth consent screen URL.

**Success Response (`200 OK`):**
```json
{
    "url": "https://accounts.google.com/o/oauth2/auth?client_id=..."
}
```

---

### 4. Google OAuth Callback (`GET /api/v1/auth/google/callback`)

Handles the callback from Google, creates a new `student` user (or links `google_id` and `avatar` to an existing user with the same email), logs the user in, and returns a Sanctum Bearer token.

**Success Response (`200 OK`):**
```json
{
    "message": "Google login successful.",
    "user": {
        "id": 1,
        "name": "Student One",
        "email": "student1@gmail.com",
        "google_id": "109876543210123456789",
        "avatar": "https://lh3.googleusercontent.com/...",
        "role_id": 3,
        "role": {
            "id": 3,
            "name": "student",
            "description": "University Student"
        }
    },
    "token": "3|gOoGlEtOkEnExAmPlE..."
}
```

---

### 5. Get Current User (`GET /api/v1/auth/user`)

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

### 6. Logout (`POST /api/v1/auth/logout`)

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

All `/api/*` endpoints return standardized JSON error responses (configured in `bootstrap/app.php`):

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
