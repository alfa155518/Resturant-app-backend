# Restaurant Management System - Backend

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)

A robust and scalable backend API for a modern restaurant management system, built with Laravel. This application provides the core functionality for managing restaurant operations including menu management, order processing, table reservations, and customer interactions.

## ✨ Features

-   **Menu Management** - Full CRUD operations for menu items, categories, and modifiers
-   **Order Processing** - Real-time order management and status updates
-   **Table Reservations** - Efficient table booking system with availability checks
-   **User Authentication** - Secure JWT-based authentication for staff and admin
-   **Role-Based Access Control** - Different permission levels for staff, managers, and admins
-   **Reporting** - Sales reports and analytics dashboard
-   **Settings Management** - Manage restaurant settings such as opening hours, contact information, and more
-   **RESTful API** - Clean, well-documented endpoints for frontend consumption

## 🛠️ Tech Stack

-   **Backend Framework**: Laravel 12.x
-   **Database**: MySQL 8.0+
-   **Authentication**: Laravel Sanctum
-   **API Documentation**: OpenAPI/Swagger
-   **Testing**: PHPUnit, Pest
-   **Deployment**: Docker, Laravel Forge/Envoyer

## 📁 Project Structure

```
backend/
├── .env                    # Environment configuration
├── .env.example           # Example environment file
├── .gitattributes         # Git attributes
├── .gitignore             # Git ignore rules
├── artisan               # Laravel command-line interface
├── composer.json         # PHP dependencies
├── composer.lock         # Locked PHP dependencies
├── package.json          # Node.js dependencies
├── phpunit.xml          # PHPUnit configuration
├── vite.config.js       # Vite configuration
│
├── app/                    # Application core
│   ├── Console/           # Artisan commands
│   ├── Exceptions/        # Exception handlers
│   ├── Http/              # HTTP layer
│   │   ├── Controllers/   # Request handlers
│   │   │   ├── Admin/     # Admin controllers
│   │   │   │   ├── Auth/  # Admin authentication
│   │   │   │   └── ...    # Other admin controllers
│   │   │   └── Auth/      # Authentication controllers
│   │   │       ├── LoginController.php
│   │   │       ├── RegisterController.php
│   │   │       └── ...
│   │   ├── Middleware/    # HTTP middleware
│   │   └── Requests/      # Form request validation
│   │
│   ├── Models/            # Database models
│   │   ├── User.php
│   │   └── Admin/         # Admin models
│   │
│   ├── Providers/         # Service providers
│   ├── Services/          # Business logic services
│   └── Traits/            # Reusable traits
│
├── config/                # Configuration files
│   ├── app.php           # Application configuration
│   ├── auth.php          # Authentication config
│   ├── database.php      # Database config
│   └── ...
│
├── database/
│   ├── factories/        # Model factories
│   ├── migrations/       # Database migrations
│   ├── seeders/          # Database seeders
│   └── ...
│
├── public/               # Publicly accessible files
│   ├── index.php        # Application entry point
│   ├── favicon.ico      # Favicon
│   └── ...
│
├── resources/
│   ├── css/             # CSS files
│   ├── js/              # JavaScript files
│   ├── lang/            # Language files
│   └── views/           # Blade templates
│
├── routes/
│   ├── api.php         # API routes
│   ├── web.php         # Web routes
│   └── admin.php       # Admin routes
│
├── storage/             # Storage directory
│   ├── app/            # Application storage
│   ├── framework/      # Framework storage
│   └── logs/           # Application logs
│
└── tests/              # Test suites
    ├── Feature/        # Feature tests
    └── Unit/           # Unit tests
```

## 📞 Contact

For support, feature requests, or any questions, please reach out to us:

-   **LinkedIn**: [Ahmed Hassop](https://www.linkedin.com/in/ahmed-hassop/)
