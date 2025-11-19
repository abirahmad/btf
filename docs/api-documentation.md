# Order Management API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

## Response Format
All API responses follow this structure:
```json
{
    "data": {},
    "message": "Success message",
    "status": "success|error",
    "errors": {}
}
```

## Endpoints

### Authentication

#### POST /register
Register a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "admin@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### POST /login
Authenticate user and get JWT token.

**Request Body:**
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "admin@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

#### POST /logout
Logout user and invalidate token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "message": "Successfully logged out"
}
```

#### POST /refresh
Refresh JWT token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

### Products

#### GET /products
List all products with pagination and search.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15)
- `search` (optional): Search term for product name, description, or SKU

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "name": "Laptop Pro 15",
            "sku": "LAP-PRO-15",
            "price": "1299.99",
            "stock_quantity": 50,
            "description": "High-performance laptop",
            "variants": {
                "color": ["Silver", "Space Gray"],
                "storage": ["256GB", "512GB"]
            },
            "is_active": true,
            "created_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "links": {},
    "meta": {}
}
```

#### GET /products/{id}
Get a specific product by ID.

**Response (200):**
```json
{
    "id": 1,
    "name": "Laptop Pro 15",
    "sku": "LAP-PRO-15",
    "price": "1299.99",
    "stock_quantity": 50,
    "description": "High-performance laptop",
    "variants": {
        "color": ["Silver", "Space Gray"],
        "storage": ["256GB", "512GB"]
    },
    "is_active": true,
    "user_id": 2,
    "created_at": "2024-01-01T00:00:00.000000Z"
}
```

#### POST /products
Create a new product (Vendor/Admin only).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Laptop Pro 15",
    "sku": "LAP-PRO-15",
    "price": 1299.99,
    "stock_quantity": 50,
    "description": "High-performance laptop",
    "variants": {
        "color": ["Silver", "Space Gray"],
        "storage": ["256GB", "512GB"]
    },
    "low_stock_threshold": 10
}
```

**Response (201):**
```json
{
    "id": 1,
    "name": "Laptop Pro 15",
    "sku": "LAP-PRO-15",
    "price": "1299.99",
    "stock_quantity": 50,
    "description": "High-performance laptop",
    "variants": {
        "color": ["Silver", "Space Gray"],
        "storage": ["256GB", "512GB"]
    },
    "is_active": true,
    "user_id": 2,
    "created_at": "2024-01-01T00:00:00.000000Z"
}
```

#### PUT /products/{id}
Update an existing product (Owner/Admin only).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Updated Laptop Pro 15",
    "price": 1199.99,
    "stock_quantity": 75
}
```

#### DELETE /products/{id}
Delete a product (Owner/Admin only).

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "message": "Product deleted successfully"
}
```

#### POST /products/import
Bulk import products from CSV (Vendor/Admin only).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```
file: products.csv
```

**CSV Format:**
```csv
name,sku,price,stock_quantity,description
"Laptop Pro 15","LAP-PRO-15",1299.99,50,"High-performance laptop"
"Wireless Mouse","WM-001",29.99,100,"Ergonomic wireless mouse"
```

**Response (200):**
```json
{
    "success": 2,
    "errors": []
}
```

### Orders

#### GET /orders
List orders (filtered by user role).

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page
- `status` (optional): Filter by order status

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "order_number": "ORD-ABC123",
            "status": "pending",
            "total_amount": "2599.98",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "items": [
                {
                    "id": 1,
                    "product_id": 1,
                    "quantity": 2,
                    "unit_price": "1299.99",
                    "total_price": "2599.98",
                    "product": {
                        "name": "Laptop Pro 15",
                        "sku": "LAP-PRO-15"
                    }
                }
            ]
        }
    ]
}
```

#### GET /orders/{id}
Get a specific order by ID.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "id": 1,
    "order_number": "ORD-ABC123",
    "status": "pending",
    "total_amount": "2599.98",
    "shipping_address": {
        "name": "John Doe",
        "street": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001"
    },
    "billing_address": {
        "street": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001"
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "items": [
        {
            "id": 1,
            "product_id": 1,
            "quantity": 2,
            "unit_price": "1299.99",
            "total_price": "2599.98",
            "product_variant": {
                "color": "Silver",
                "storage": "512GB"
            },
            "product": {
                "name": "Laptop Pro 15",
                "sku": "LAP-PRO-15"
            }
        }
    ]
}
```

#### POST /orders
Create a new order.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "variant": {
                "color": "Silver",
                "storage": "512GB"
            }
        }
    ],
    "shipping_address": {
        "name": "John Doe",
        "street": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001"
    },
    "billing_address": {
        "street": "123 Main St",
        "city": "New York",
        "state": "NY",
        "zip": "10001"
    }
}
```

**Response (201):**
```json
{
    "id": 1,
    "order_number": "ORD-ABC123",
    "status": "pending",
    "total_amount": "2599.98",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "items": [...]
}
```

#### PATCH /orders/{id}/status
Update order status (Admin/Vendor only).

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "status": "shipped"
}
```

**Valid Statuses:**
- `pending`
- `processing`
- `shipped`
- `delivered`
- `cancelled`

#### PATCH /orders/{id}/cancel
Cancel an order.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
    "message": "Order cancelled successfully"
}
```

#### GET /orders/{id}/invoice
Download order invoice as PDF.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
- Content-Type: `application/pdf`
- File download with filename: `invoice-{order_number}.pdf`

## Error Responses

### Validation Error (422)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Unauthorized (401)
```json
{
    "error": "Invalid credentials"
}
```

### Forbidden (403)
```json
{
    "error": "This action is unauthorized."
}
```

### Not Found (404)
```json
{
    "error": "Resource not found"
}
```

### Server Error (500)
```json
{
    "error": "Internal server error"
}
```

## Rate Limiting

API endpoints are rate limited:
- **Authenticated users:** 60 requests per minute
- **Guest users:** 30 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

## Pagination

List endpoints support pagination with these parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination metadata is included in the response:
```json
{
    "data": [...],
    "links": {
        "first": "http://localhost:8000/api/v1/products?page=1",
        "last": "http://localhost:8000/api/v1/products?page=10",
        "prev": null,
        "next": "http://localhost:8000/api/v1/products?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 10,
        "per_page": 15,
        "to": 15,
        "total": 150
    }
}
```