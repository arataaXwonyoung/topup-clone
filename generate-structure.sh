#!/bin/bash
# generate-structure.sh
# Jalankan dari root project (takapedia-clone/)

# ===== APP =====
mkdir -p app/Console/Commands
touch app/Console/Commands/ExpireOrdersCommand.php
touch app/Console/Kernel.php

mkdir -p app/Events
touch app/Events/PaymentStatusUpdated.php

mkdir -p app/Exceptions
touch app/Exceptions/Handler.php

mkdir -p app/Http/Controllers/Admin
mkdir -p app/Http/Controllers/Auth
touch app/Http/Controllers/HomeController.php
touch app/Http/Controllers/GameController.php
touch app/Http/Controllers/CheckoutController.php
touch app/Http/Controllers/InvoiceController.php
touch app/Http/Controllers/TransactionLookupController.php
touch app/Http/Controllers/WebhookController.php
touch app/Http/Controllers/Controller.php

mkdir -p app/Http/Middleware
touch app/Http/Middleware/VerifyWebhookSignature.php

mkdir -p app/Http/Requests
touch app/Http/Requests/CheckoutRequest.php
touch app/Http/Requests/ApplyPromoRequest.php
touch app/Http/Requests/ProfileUpdateRequest.php

touch app/Http/Kernel.php

mkdir -p app/Jobs
touch app/Jobs/AutoFulfillmentJob.php
touch app/Jobs/CloseExpiredOrdersJob.php

mkdir -p app/Listeners
touch app/Listeners/ProcessPaymentUpdate.php

mkdir -p app/Models
touch app/Models/Game.php
touch app/Models/Denomination.php
touch app/Models/Promo.php
touch app/Models/Order.php
touch app/Models/Payment.php
touch app/Models/WebhookLog.php
touch app/Models/Review.php
touch app/Models/Article.php
touch app/Models/Banner.php
touch app/Models/User.php

mkdir -p app/Policies

mkdir -p app/Providers/Filament
touch app/Providers/AppServiceProvider.php
touch app/Providers/AuthServiceProvider.php
touch app/Providers/BroadcastServiceProvider.php
touch app/Providers/EventServiceProvider.php
touch app/Providers/Filament/AdminPanelProvider.php
touch app/Providers/RouteServiceProvider.php

mkdir -p app/Services/Payment/Drivers
mkdir -p app/Services/Payment/DTOs
touch app/Services/Payment/PaymentService.php
touch app/Services/Payment/PaymentManager.php
touch app/Services/Payment/Drivers/MidtransDriver.php
touch app/Services/Payment/Drivers/XenditDriver.php
touch app/Services/Payment/Drivers/TripayDriver.php
touch app/Services/Payment/DTOs/CreateChargeRequest.php
touch app/Services/Payment/DTOs/PaymentResponse.php
touch app/Services/PromoService.php
touch app/Services/OrderService.php

# ===== CONFIG =====
touch config/payment.php
touch config/filament.php

# ===== DATABASE =====
mkdir -p database/factories
touch database/factories/UserFactory.php
touch database/factories/GameFactory.php
touch database/factories/DenominationFactory.php
touch database/factories/OrderFactory.php

mkdir -p database/migrations
touch database/migrations/2014_10_12_000000_create_users_table.php
touch database/migrations/2024_01_01_000001_create_games_table.php
touch database/migrations/2024_01_01_000002_create_denominations_table.php
touch database/migrations/2024_01_01_000003_create_promos_table.php
touch database/migrations/2024_01_01_000004_create_orders_table.php
touch database/migrations/2024_01_01_000005_create_payments_table.php
touch database/migrations/2024_01_01_000006_create_webhook_logs_table.php
touch database/migrations/2024_01_01_000007_create_reviews_table.php
touch database/migrations/2024_01_01_000008_create_articles_table.php
touch database/migrations/2024_01_01_000009_create_banners_table.php
touch database/migrations/2024_01_01_000010_add_is_admin_to_users_table.php

mkdir -p database/seeders
touch database/seeders/DatabaseSeeder.php
touch database/seeders/GameSeeder.php
touch database/seeders/DenominationSeeder.php
touch database/seeders/PromoSeeder.php
touch database/seeders/DemoOrderSeeder.php

# ===== PUBLIC =====
mkdir -p public/images/games
mkdir -p public/images/banners
touch public/images/og-image.jpg
touch public/images/logo.png
touch public/images/qris-logo.png

# ===== RESOURCES =====
mkdir -p resources/css
touch resources/css/app.css

mkdir -p resources/js
touch resources/js/app.js
touch resources/js/bootstrap.js

mkdir -p resources/views/layouts
touch resources/views/layouts/app.blade.php

mkdir -p resources/views/components
touch resources/views/components/stepper.blade.php
touch resources/views/components/game-card.blade.php
touch resources/views/components/denom-card.blade.php
touch resources/views/components/price-summary.blade.php
touch resources/views/components/rating-stars.blade.php

mkdir -p resources/views/games
touch resources/views/games/show.blade.php

mkdir -p resources/views/invoices
touch resources/views/invoices/show.blade.php
touch resources/views/invoices/pdf.blade.php

mkdir -p resources/views/pages
touch resources/views/pages/leaderboard.blade.php
touch resources/views/pages/check-transaction.blade.php
touch resources/views/pages/calculator.blade.php

mkdir -p resources/views/articles
touch resources/views/articles/index.blade.php
touch resources/views/articles/show.blade.php

mkdir -p resources/views/auth
touch resources/views/auth/login.blade.php
touch resources/views/auth/register.blade.php
touch resources/views/auth/forgot-password.blade.php
touch resources/views/auth/reset-password.blade.php
touch resources/views/auth/verify-email.blade.php

mkdir -p resources/views/profile/partials
touch resources/views/profile/edit.blade.php

mkdir -p resources/views/orders
touch resources/views/orders/history.blade.php

touch resources/views/home.blade.php
touch resources/views/welcome.blade.php

# ===== ROUTES =====
touch routes/auth.php

# ===== TESTS =====
mkdir -p tests/Feature/Auth
touch tests/Feature/CheckoutTest.php
touch tests/Feature/WebhookTest.php
touch tests/Feature/PromoTest.php

mkdir -p tests/Unit
touch tests/Unit/PromoServiceTest.php
touch tests/Unit/PaymentServiceTest.php
touch tests/Unit/OrderServiceTest.php
