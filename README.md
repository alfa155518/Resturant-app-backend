# Restaurant Management System - Backend

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)

A robust and scalable backend API for a modern restaurant management system, built with Laravel. This application provides the core functionality for managing restaurant operations including menu management, order processing, table reservations, and customer interactions.

## ✨ Features

- **Menu Management** - Full CRUD operations for menu items, categories, and modifiers
- **Order Processing** - Real-time order management and status updates
- **Table Reservations** - Efficient table booking system with availability checks
- **User Authentication** - Secure JWT-based authentication for staff and admin
- **Role-Based Access Control** - Different permission levels for staff, managers, and admins
- **Reporting** - Sales reports and analytics dashboard
- **RESTful API** - Clean, well-documented endpoints for frontend consumption

## 🛠️ Tech Stack

- **Backend Framework**: Laravel 10.x
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **API Documentation**: OpenAPI/Swagger
- **Testing**: PHPUnit, Pest
- **Deployment**: Docker, Laravel Forge/Envoyer

## 🚀 Getting Started

### Prerequisites

- PHP 8.1+
- Composer
- MySQL 8.0+
- Node.js 16+ & NPM

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/restaurant-app-backend.git
   cd restaurant-app-backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Copy environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your `.env` file with database credentials and other settings.

7. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

## 🔧 Environment Variables

Create a `.env` file in the root directory with the following variables:

```env
APP_NAME="Restaurant App"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_db
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=
```

## 🧪 Testing

Run the tests with:

```bash
php artisan test
```

## 📚 API Documentation

After starting the development server, access the API documentation at:

```
http://localhost:8000/api/documentation
```

## 🐳 Docker Support

To run the application using Docker:

1. Make sure Docker and Docker Compose are installed
2. Run:
   ```bash
   docker-compose up -d
   ```
3. The application will be available at `http://localhost:8000`

## 🤝 Contributing

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👏 Acknowledgements

- [Laravel](https://laravel.com/)
- [Laravel Sanctum](https://laravel.com/sanctum)
- [OpenAPI](https://swagger.io/)
- [Docker](https://www.docker.com/)
