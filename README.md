# F3 + Vue 3 Project

## 🚀 Project Status

Backend API with F3 (Fat-Free Framework) is now fully configured and operational.

### Current State
- ✅ Repository cleaned and reset
- ✅ Git history preserved
- ✅ F3 backend framework configured
- ✅ Database connection (SQLite)
- ✅ Session management
- ✅ Error handling
- ✅ CORS configuration
- ✅ API routing system
- ✅ All tests passing (100%)
- 🔲 Vue 3 frontend (pending)
- 🔲 Database schema (pending)

---

## 🏗️ Architecture

### Backend
- **Framework**: Fat-Free Framework (F3) v3.9.1
- **Language**: PHP 8.3
- **Database**: SQLite (development) - can switch to MySQL
- **Session**: PHP native sessions
- **API Style**: RESTful JSON

### Frontend (Pending)
- **Framework**: Vue 3
- **Build Tools**: Modern JavaScript build pipeline

---

## 🚀 Quick Start

### Backend API Server

Start the development server:

```bash
php -S localhost:8000 -t api
```

The API will be available at:
- **Health Check**: http://localhost:8000
- **Status**: http://localhost:8000/status
- **Test Suite**: http://localhost:8000/test.php

### Testing

Run the test suite:

```bash
php api/test.php
```

All 6 tests should pass with 100% success rate.

---

## 📁 Project Structure

```
.
├── api/                      # Backend API (F3 Framework)
│   ├── lib/                  # F3 framework core files
│   │   ├── base.php          # F3 core (~96KB)
│   │   └── db/               # Database classes
│   ├── services/             # Service layer
│   │   ├── Database.php      # Database CRUD operations
│   │   ├── ErrorHandler.php  # Unified error handling
│   │   └── Session.php       # Session management
│   ├── config.php            # Configuration file
│   ├── index.php             # API entry point
│   ├── routes.php            # Route definitions
│   ├── test.php              # Test suite
│   ├── .htaccess             # Apache rewrite rules
│   └── README.md             # Backend documentation
├── data/                     # Database files (SQLite)
│   └── database.sqlite
├── logs/                     # Application logs
├── tmp/                      # Temporary files
└── README.md                 # This file
```

---

## 🔌 API Endpoints

### Base Routes
- `GET /` - Health check
- `GET /status` - Server status and info

### Authentication (Placeholders)
- `POST /auth/login` - Admin login
- `POST /auth/logout` - Admin logout
- `GET /auth/check` - Check auth status

### Resources (Placeholders)
- `GET|POST /staff` - Staff management
- `GET|POST /interviews` - Interview records
- `GET|POST /checklists` - Checklist management

All placeholder routes return 501 with Japanese error messages.

---

## ⚙️ Configuration

### Database

Configure in `api/config.php`:

```php
// SQLite (default)
$f3->set('DB', new \DB\SQL(
    'sqlite:' . __DIR__ . '/../data/database.sqlite'
));

// Or MySQL
$f3->set('DB', new \DB\SQL(
    'mysql:host=localhost;port=3306;dbname=your_database',
    'username',
    'password'
));
```

### Session

Session timeout is configurable in `api/config.php`:

```php
$f3->set('SESSION', [
    'timeout' => 3600, // 1 hour in seconds
    // ... other settings
]);
```

### CORS

CORS settings in `api/config.php`:

```php
$f3->set('CORS', [
    'origin' => '*',                                    // Allowed origins
    'methods' => 'GET, POST, PUT, DELETE, OPTIONS',    // Allowed methods
    'headers' => 'Content-Type, Authorization',        // Allowed headers
    'credentials' => 'true'                            // Allow credentials
]);
```

### Debug Mode

Set debug level in `api/config.php`:

```php
$f3->set('DEBUG', 3);  // 0 = production, 3 = verbose debug
```

---

## 🛠️ Development

### Adding New Endpoints

1. **Create a service class** (optional) in `api/services/`
2. **Add routes** in `api/routes.php`
3. **Test** using curl or the test suite

Example:

```php
// In api/routes.php
$f3->route('GET /users', function($f3) use ($errorHandler) {
    $db = new \Services\Database();
    $users = $db->select('users');
    $errorHandler->success($users, 'ユーザーリストを取得しました');
});
```

### Service Classes

Three core services are available:

1. **Database** (`Services\Database`)
   - CRUD operations
   - Transactions
   - Query helpers

2. **ErrorHandler** (`Services\ErrorHandler`)
   - Unified responses
   - Validation helpers
   - HTTP status codes

3. **Session** (`Services\Session`)
   - Login/logout
   - Session validation
   - User data management

See `api/README.md` for detailed documentation.

---

## 🧪 Testing

The test suite validates:
- ✅ F3 framework loading
- ✅ Timezone configuration (Asia/Tokyo)
- ✅ Database connection
- ✅ Database service CRUD operations
- ✅ Session management
- ✅ Error handler and validators

Run tests:

```bash
php api/test.php
```

Or visit: http://localhost:8000/test.php

---

## 📚 Documentation

- **Backend API**: See `api/README.md` for detailed backend documentation
- **F3 Framework**: https://fatfreeframework.com/
- **PHP Manual**: https://www.php.net/manual/

---

## 🔐 Security Notes

### Production Checklist

Before deploying to production:

1. **Disable debug mode**: `$f3->set('DEBUG', 0);`
2. **Enable HTTPS**: `$f3->set('SESSION.secure', true);`
3. **Restrict CORS**: Set specific domains instead of `*`
4. **Protect config files**: Ensure `.htaccess` blocks access
5. **Use environment variables**: Store sensitive data in `.env`
6. **Set file permissions**: 
   - `chmod 755 data/`
   - `chmod 664 data/database.sqlite`
   - `chmod 755 logs/`

---

## 📝 Code Conventions

- **Comments**: Chinese (中文) for code documentation
- **Error Messages**: Japanese (日本語) for user-facing errors
- **Response Format**: JSON with `success`, `message`, `data/error`, `code`
- **Timezone**: Asia/Tokyo
- **Naming**: 
  - Service classes: PascalCase
  - Methods: camelCase
  - Database tables: snake_case

---

## 🤝 Contributing

When contributing:

1. Follow existing code style and conventions
2. Add Chinese comments for complex logic
3. Use Japanese for user-facing messages
4. Test all changes with the test suite
5. Update documentation as needed

---

## 📄 License

[To be determined]

---

## 📞 Support

For issues or questions about the F3 backend:
- Check `api/README.md` for detailed documentation
- Review F3 official docs: https://fatfreeframework.com/
- Run test suite to verify setup

---

**Version**: 1.0.0  
**Last Updated**: 2024-11-18  
**PHP Version**: 8.3+  
**F3 Version**: 3.9.1
