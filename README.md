# Live Demo

## Frontend Application
- Nuxt: [http://31.220.111.227:3005/](http://31.220.111.227:3005/)

## Backend Administration
- Laravel Admin Panel: [http://31.220.111.227:8000](http://31.220.111.227:8000)
  - Username: `admin@admin.com`
  - Password: `admin@admin.com`

## Instructions
1. Log in to the Laravel admin panel using the credentials above
2. Create a new organization
3. Activate the organization to generate a clickable subdomain link
4. Access the subdomain using the same credentials



# Quick Local Setup

## 1. Laravel Backend

```bash
# Navigate to Laravel folder
cd laravel

# Copy environment file
cp .env.example .env

# Install dependencies
composer install

# Generate app key
php artisan key:generate

# Edit .env file for database connection
# Then run migrations
php artisan migrate

# Start Laravel server
php artisan serve
php artisan queue:work
```

Laravel will run on: http://localhost:8000

## 2. Nuxt Frontend

```bash
# Navigate to Nuxt folder (from project root)
cd nuxt

# Copy environment file
cp .env.example .env

# Install dependencies
npm install

# Start development server
npm run dev
```

Nuxt will run on: http://localhost:3000

## Database Setup (PostgreSQL)

```bash
# Create database
sudo -u postgres createdb solutech_events

# Update Laravel .env file with:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=solutech_events
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

That's it! Both applications should be running locally.
