# Order Management System API

A scalable REST API for order management with inventory tracking, built with Laravel 12 and following SOLID principles.

## 🚀 Features

### Product & Inventory Management
- ✅ Product CRUD with variants support
- ✅ Real-time inventory tracking with logs
- ✅ Low stock alerts via queue jobs
- ✅ Bulk product import via CSV
- ✅ Product search functionality

### Order Processing
- ✅ Create orders with multiple items
- ✅ Order status workflow: Pending → Processing → Shipped → Delivered → Cancelled
- ✅ Automatic inventory deduction on order confirmation
- ✅ Order rollback on cancellation (restore inventory)
- ✅ PDF invoice generation
- ✅ Email notifications for order updates

### Authentication & Authorization
- ✅ JWT authentication with refresh tokens
- ✅ Role-based access control (Admin, Vendor, Customer)
- ✅ Granular permissions system

### Technical Features
- ✅ Service classes for business logic
- ✅ Repository pattern for data access
- ✅ Actions/Commands for complex operations
- ✅ Events & Listeners for decoupled logic
- ✅ Queue jobs for async operations
- ✅ Database transactions for data integrity
- ✅ API versioning (v1)
- ✅ Comprehensive testing suite

## 🛠 Tech Stack

- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Database:** MySQL/PostgreSQL
- **Authentication:** JWT (tymon/jwt-auth)
- **PDF Generation:** DomPDF
- **Permissions:** Spatie Laravel Permission
- **Search:** Laravel Scout
- **Queue:** Database/Redis
- **Testing:** PHPUnit

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL
- Node.js & NPM (for asset compilation)
- Redis (optional, for caching and queues)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd BTF
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configure Environment Variables
Edit `.env` file with your database and other configurations:

```env
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=your_password

# JWT Configuration
JWT_SECRET=your_jwt_secret
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

# Queue Configuration
QUEUE_CONNECTION=database
```

### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 6. Storage Setup
```bash
php artisan storage:link
mkdir -p storage/app/invoices
```

### 7. Install Additional Packages
```bash
composer require tymon/jwt-auth barryvdh/laravel-dompdf spatie/laravel-permission league/csv laravel/scout pusher/pusher-php-server
```

### 8. Publish Package Configurations
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## 🏃‍♂️ Running the Application

### Development Server
```bash
php artisan serve
```

### Queue Worker (for background jobs)
```bash
php artisan queue:work
```

### Asset Compilation
```bash
npm run dev
```

## 🔐 Authentication

### Default Users
After seeding, you can use these test accounts:

**Admin:**
- Email: admin@example.com
- Password: password123

**Vendor:**
- Email: vendor@example.com
- Password: password123

**Customer:**
- Email: customer@example.com
- Password: password123

### API Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer your_jwt_token
```

## 📚 API Documentation

### Swagger Documentation
Access the interactive API documentation at: `http://localhost:8000/api/documentation`

The Swagger UI provides:
- Complete API endpoint documentation
- Interactive testing interface
- Request/response examples
- Authentication testing with JWT tokens

### Postman Collection
Import the complete Postman collection: `docs/Order-Management-API-Complete.postman_collection.json`

Features:
- Auto JWT token management
- Pre-filled example requests
- Complete workflow testing
- Rate limiting headers
- 15 endpoints with examples

### Authentication Endpoints

#### Register
```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### Refresh Token
```http
POST /api/v1/refresh
Authorization: Bearer your_jwt_token
```

### Product Endpoints

#### List Products
```http
GET /api/v1/products?page=1&per_page=15&search=laptop
```

#### Create Product (Vendor/Admin only)
```http
POST /api/v1/products
Authorization: Bearer your_jwt_token
Content-Type: application/json

{
    "name": "Laptop Pro 15",
    "sku": "LAP-PRO-15",
    "price": 1299.99,
    "stock_quantity": 50,
    "description": "High-performance laptop",
    "variants": {
        "color": ["Silver", "Space Gray"],
        "storage": ["256GB", "512GB"]
    }
}
```

#### Import Products (CSV)
```http
POST /api/v1/products/import
Authorization: Bearer your_jwt_token
Content-Type: multipart/form-data

file: products.csv
```

### Order Endpoints

#### Create Order
```http
POST /api/v1/orders
Authorization: Bearer your_jwt_token
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "variant": {"color": "Silver", "storage": "512GB"}
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

#### Update Order Status (Admin/Vendor only)
```http
PATCH /api/v1/orders/{id}/status
Authorization: Bearer your_jwt_token
Content-Type: application/json

{
    "status": "shipped"
}
```

#### Cancel Order
```http
PATCH /api/v1/orders/{id}/cancel
Authorization: Bearer your_jwt_token
```

#### Download Invoice
```http
GET /api/v1/orders/{id}/invoice
Authorization: Bearer your_jwt_token
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# With coverage
php artisan test --coverage
```

### Test Database
Tests use a separate SQLite database for isolation. No additional setup required.

## 📊 Database Schema

### Key Tables
- `users` - User accounts with roles
- `products` - Product catalog with variants
- `orders` - Order records
- `order_items` - Order line items
- `inventory_logs` - Stock movement tracking
- `roles` & `permissions` - Authorization system

### Indexes
- Products: `name`, `sku`, `stock_quantity`, `is_active`
- Orders: `order_number`, `status`, `user_id`
- Order Items: `order_id`, `product_id`
- Inventory Logs: `product_id`, `type`

## 🔄 Queue Jobs

### Background Jobs
- `SendOrderEmailJob` - Email notifications
- `CheckLowStockJob` - Inventory monitoring
- `GenerateInvoiceJob` - PDF generation

### Running Jobs
```bash
# Process jobs once
php artisan queue:work --once

# Process jobs continuously
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=emails,default
```

## 📈 Performance Optimization

### Database Optimization
- Proper indexing on searchable fields
- Eager loading to prevent N+1 queries
- Database transactions for data integrity
- Query optimization with repository pattern

### Caching Strategy
- Redis for session and cache storage
- API response caching for product listings
- Query result caching for frequently accessed data

### Scaling Considerations
- Horizontal scaling with load balancers
- Database read replicas for heavy read operations
- Queue workers on separate servers
- CDN for static assets

## 🛡 Security Features

- JWT token authentication
- Role-based access control
- Input validation and sanitization
- SQL injection prevention via Eloquent ORM
- CSRF protection
- Rate limiting on API endpoints

## 📝 API Rate Limiting

Default rate limits:
- 60 requests per minute for authenticated users
- 30 requests per minute for guest users

Configure in `.env`:
```env
API_RATE_LIMIT=60
API_RATE_LIMIT_WINDOW=1
```

## 🚀 Deployment

### Production Checklist
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Configure proper database credentials
4. Set up Redis for caching and queues
5. Configure mail settings
6. Set up SSL certificates
7. Configure web server (Nginx/Apache)
8. Set up queue workers as system services
9. Configure log rotation
10. Set up monitoring and alerts

### Docker Deployment
```bash
# Build and run with Docker Compose
docker-compose up -d

# Run migrations in container
docker-compose exec app php artisan migrate --force
```

## 👨‍💻 Developer Information

**Name:** [Your Name]  
**Email:** [your.email@example.com]  
**GitHub:** [https://github.com/yourusername]  
**LinkedIn:** [https://linkedin.com/in/yourprofile]

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Email: [your.email@example.com]
- Documentation: [Link to detailed API docs]

## 🔄 Changelog

### v1.0.0 (2024-01-01)
- Initial release
- Complete order management system
- JWT authentication
- Role-based access control
- Inventory tracking
- PDF invoice generation
- Comprehensive testing suite