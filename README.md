# Laravel + Vue 3 + Inertia Application

A modern web application built with Laravel 10, Vue 3, Inertia.js, and Tailwind CSS, scaffolded with Laravel Breeze.

## Tech Stack

- **Backend**: Laravel 10
- **Frontend**: Vue 3 with Composition API
- **Bridge**: Inertia.js
- **Styling**: Tailwind CSS
- **Database**: SQLite (default)
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze

## Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.1 with the following extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - SQLite3
- **Composer** >= 2.0
- **Node.js** >= 16.x and npm

## Installation

Follow these steps to set up the application locally:

### 1. Clone the repository

```bash
git clone <your-repository-url>
cd <your-project-directory>
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node.js dependencies

```bash
npm install
```

### 4. Environment configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

### 5. Database setup

The application is configured to use SQLite by default. Create the database file:

```bash
touch database/database.sqlite
```

Run the database migrations:

```bash
php artisan migrate
```

### 6. Create the initial admin user

You can create an initial admin user using one of the following methods:

#### Using the Artisan command (interactive):

```bash
php artisan admin:create
```

This will prompt you for the admin's email, password, and name.

#### Using the Artisan command (with options):

```bash
php artisan admin:create --email=admin@example.com --password=your-password --name="Admin User"
```

#### Using the database seeder:

```bash
php artisan db:seed --class=AdminUserSeeder
```

This will create an admin user with the credentials:
- **Email**: admin@example.com
- **Password**: password
- **Name**: Admin User

## Authentication & Authorization

This application implements role-based access control (RBAC) with admin-only protected routes.

### Features

- **Email/Password Authentication**: Only email and password login is enabled (registration is disabled)
- **Role-Based Access Control**: Users must have the `admin` role to access protected routes
- **Admin Dashboard**: Protected by both authentication and admin role verification
- **Sanctum Integration**: Configured for SPA authentication

### Admin Role

The system uses a single `admin` role by default. All protected routes require users to have this role.

### Usage

```php
// Check if a user is an admin
if (auth()->user()->isAdmin()) {
    // User is admin
}

// Check if a user has a specific role
if (auth()->user()->hasRole('admin')) {
    // User has admin role
}
```

## Development

### Running the development servers

You need to run both the backend and frontend development servers simultaneously:

#### Terminal 1 - Backend Server

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

#### Terminal 2 - Frontend Build

Start the Vite development server for hot module replacement:

```bash
npm run dev
```

### Building for production

To build the frontend assets for production:

```bash
npm run build
```

## Testing

Run the test suite:

```bash
php artisan test
```

Run tests with coverage:

```bash
php artisan test --coverage
```

## Available Features

This application provides the following authentication and authorization features:

- **Admin-only authentication**: Email/password login (registration disabled)
- **Role-Based Access Control (RBAC)**: Admin role enforcement on protected routes
- **Login/logout**: Admin session management
- **Password reset**: Secure password recovery
- **Email verification**: Verified email requirement
- **Profile management**: Admin profile settings
- **Password confirmation**: Sensitive action protection
- **Laravel Sanctum**: SPA authentication support

## Project Structure

```
.
├── app/                    # Laravel application code
├── bootstrap/              # Framework bootstrap files
├── config/                 # Configuration files
├── database/              
│   ├── migrations/         # Database migrations
│   └── database.sqlite     # SQLite database file
├── public/                 # Public assets and entry point
├── resources/
│   ├── js/                 # Vue 3 components and Inertia pages
│   ├── css/                # Stylesheets
│   └── views/              # Blade templates
├── routes/                 # Application routes
├── storage/                # Application storage
├── tests/                  # Automated tests
└── vendor/                 # Composer dependencies
```

## Key Commands

| Command | Description |
|---------|-------------|
| `composer install` | Install PHP dependencies |
| `npm install` | Install Node.js dependencies |
| `php artisan serve` | Start Laravel development server |
| `npm run dev` | Start Vite development server with HMR |
| `npm run build` | Build assets for production |
| `php artisan migrate` | Run database migrations |
| `php artisan admin:create` | Create an initial admin user |
| `php artisan test` | Run automated tests |
| `php artisan tinker` | Open Laravel REPL |
| `php artisan route:list` | List all registered routes |

## Useful Links

- [Laravel Documentation](https://laravel.com/docs/10.x)
- [Vue 3 Documentation](https://vuejs.org/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Laravel Breeze Documentation](https://laravel.com/docs/10.x/starter-kits#laravel-breeze)

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
