# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Takapedia Clone is a Laravel-based game top-up platform that allows users to purchase in-game credits and digital products. The application supports multiple payment providers and includes an admin panel built with Filament.

## Development Commands

### PHP/Laravel Commands
- `php artisan serve` - Start development server
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migration with seeders
- `php artisan test` - Run PHPUnit tests
- `vendor/bin/phpunit` - Alternative test command
- `php artisan queue:work` - Process background jobs
- `php artisan schedule:work` - Run scheduled tasks in development
- `php artisan tinker` - Laravel REPL for testing
- `php artisan key:generate` - Generate application key
- `php artisan storage:link` - Create storage symbolic link

### Frontend Commands
- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

### Code Quality
- `vendor/bin/pint` - Laravel Pint code formatting (equivalent to `php artisan pint`)

## Application Architecture

### Core Models & Relationships
- **Game** - Core game entities with denominations, orders, and reviews
- **Order** - Purchase orders with auto-generated invoice numbers and status tracking
- **Payment** - Payment records linked to orders with webhook support
- **User** - Authentication with admin/user roles
- **Denomination** - Game product variants with pricing
- **Review** - User reviews for games with approval system

### Payment System
The application uses a multi-provider payment architecture:
- **PaymentManager** - Factory pattern for payment providers
- **PaymentService** interface - Contract for payment implementations
- **Drivers**: MidtransDriver, XenditDriver, TripayDriver
- **Webhook handling** - Centralized webhook processing at `/webhooks/{provider}`
- **Configuration** - Provider settings in `config/payment.php`

### Admin Panel (Filament)
- Located at `/admin` route
- Custom dashboard with analytics widgets
- Resource management for games, orders, users
- System logs and webhook monitoring
- Access controlled by `is_admin` user attribute

### Frontend Stack
- **Laravel Breeze** - Authentication scaffolding
- **Tailwind CSS + DaisyUI** - Styling framework
- **Alpine.js** - Frontend reactivity
- **Vite** - Asset bundling
- **GSAP & Canvas Confetti** - Animations

### Background Jobs
- `AutoFulfillmentJob` - Automatic order processing
- `CloseExpiredOrdersJob` - Order expiration handling
- Queue system supports order processing and notifications

### Key Services
- **OrderService** - Order creation and management logic
- **PaymentService** - Payment processing abstraction
- **NotificationService** - User notification handling
- **PromoService** - Promotional code management

## Database
- **SQLite** - Default database (database/database.sqlite)
- **Migrations** - Located in database/migrations/
- **Seeders** - Demo data and admin user setup
- **Factories** - Model factories for testing

## File Structure Notes
- **app/Filament/** - Admin panel customizations
- **app/Services/Payment/** - Payment provider implementations
- **resources/views/user/** - User dashboard views
- **routes/web.php** - Contains both public and authenticated routes
- **config/payment.php** - Payment provider configuration
- **public/images/games/** - Game cover images

## Authentication & Authorization
- Uses Laravel Breeze for authentication
- Role-based access with `is_admin` boolean on users
- Separate user and admin dashboards
- Admin routes redirect to Filament panel

## Testing
- PHPUnit test suite in tests/ directory
- Feature tests for authentication, checkout, webhooks
- Unit tests for services (OrderService, PaymentService, PromoService)