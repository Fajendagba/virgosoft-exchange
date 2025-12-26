# Virgosoft Exchange - Limit Order Engine

A Laravel 12 real-time limit order exchange engine featuring financial-grade concurrency safety and atomic matching. Built with Laravel 12 and PHP 8.3.

## 📋 Prerequisites
* PHP 8.3+
* Composer
* MySQL 8.0+
* Pusher Account (Free tier)

## ⚙️ Setup Steps

### 1. Installation
Clone the repository and install PHP dependencies:
```bash
git clone [https://github.com/fajendagba/virgosoft-exchange.git](https://github.com/fajendagba/virgosoft-exchange.git)
cd virgosoft-exchange
composer install

```

### 2. Configuration

Copy the environment file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate

```

**⚠️ Important:** Open `.env` and configure your Database and Pusher credentials:

```ini
DB_DATABASE=virgosoft_exchange
DB_USERNAME=root
DB_PASSWORD=

# Required for Real-time features
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1

```

### 3. Database & Seeding

Create the database and run the migrations. The seeder will automatically set up Laravel Passport clients and demo users.

```bash
# Ensure MySQL is running, then:
php artisan migrate:fresh --seed

```

### 4. Running the Application

**Crucial:** You must run two separate processes for the exchange to function correctly (handling broadcasting events).

**Terminal 1 (Queue Worker for Broadcasts):**

```bash
php artisan queue:work

```

**Terminal 2 (API Server):**

```bash
php artisan serve

```

### Run Automated Tests

```bash
php artisan test

```

### Manual Testing / Demo Credentials

The database is seeded with two users Alice (seller) and Bob (buyer) to test matching logic:


**Test Flow:**

1. Login as **Alice** and place a **Sell** order (e.g., BTC @ $95,000).
2. Login as **Bob** and place a matching **Buy** order.
3. Check the Pusher Debug Console to see the real-time trade event.
