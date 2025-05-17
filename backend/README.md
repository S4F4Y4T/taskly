# 📝 Taskly API Documentation

Taskly is a task management API powered by **Laravel** and **Sanctum** for secure token-based authentication. The project uses **Docker Compose** for easy environment setup and includes a **Postman collection** for testing all available endpoints.

---

## 📦 Features

- 🧪 Laravel Sanctum Token-Based Authentication
- 🐳 Docker Compose for Local Development
- 🔐 Protected Routes with `auth:sanctum` Middleware
- 🔁 Login, Logout, and User Info APIs
- 📬 Postman Collection Included
- Test Cases
- Sentry integrated for error tracking
- Versioning

---

## 🚀 Getting Started

### 🐳 Docker Setup

1. Clone the repository:

```bash
git clone https://github.com/S4F4Y4T/taskly.git
cd taskly
```

2. Copy `.env` and configure:

```bash
cp .env.example .env
```

3. Start containers:

```bash
docker-compose up -d --build
```

4. Install dependencies & migrate:

```bash
docker exec -it taskly_backend php artisan migrate
```

---

## 📡 API Endpoints

### 🔓 Login

**Endpoint**: `POST /api/v1/login`  
**Body**:

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response**:

```json
{
    "code": 200,
    "type": "success",
    "message": "Logged in successfully",
    "data": {
        "token": "api-token"
    }
}
```

---

### 🔐 Logout

**Endpoint**: `POST /api/v1/logout`  
**Header**:

```
Authorization: Bearer {token}
```

**Response**:

```json
{
    "code": 200,
    "type": "success",
    "message": "Logged out successfully"
}
```

---

### 👤 Get Authenticated User

**Endpoint**: `GET /api/v1/user`  
**Header**:

```
Authorization: Bearer {token}
```

**Response**:

```json
{
    "data": {
        "id": 1,
        "name": "test",
        "email": "test@example.com",
        "email_verified_at": null,
        "created_at": "2025-05-17T05:50:41.000000Z",
        "updated_at": "2025-05-17T05:50:41.000000Z"
    }
}
```

---

## 🪪 Token Usage

Client must send the token in HTTP headers:

```
Authorization: Bearer your-access-token
```

---

## 🧪 Postman Collection

A ready-to-use Postman collection is included at:

```
postman/Taskly.postman_collection.json
```

> Import it into Postman to test all available endpoints quickly.

## 🧪 Automatic Test

use automatic test
```
php artisan test
```
