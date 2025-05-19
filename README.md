# Solutech Events Application

This repository contains a Nuxt.js frontend and Laravel API backend for the Solutech Events application.

## Docker Setup

The application is containerized using Docker and can be easily deployed in any environment.

### Prerequisites

- Docker
- Docker Compose

### Project Structure

- `laravel/`: The Laravel backend API
- `nuxt/`: The Nuxt.js frontend application
- `docker-compose.yml`: Configuration for all services

### Services

- **Frontend (Nuxt)**: Runs on port 3000
- **Backend API (Laravel)**: Runs on port 8000
- **Database (PostgreSQL)**: Runs on port 5432

### Environment Variables

The application uses environment variables for configuration. These are set in the docker-compose.yml file.

### Getting Started

1. Clone this repository
2. Navigate to the project root directory
3. Run the deployment script:

```bash
./deploy.sh
```

This script will:
- Stop any running containers
- Build fresh Docker images
- Start all services
- Display running containers

When the containers start, the Laravel backend will automatically:
- Wait for the PostgreSQL database to be ready
- Run migrations (`php artisan migrate --force`)
- Seed the database (`php artisan db:seed --force`)
- Run tenant migrations (`php artisan tenants:migrate`)
- Build frontend assets (`npm run build`)
- Start queue workers (`php artisan queue:work`)

Alternatively, you can run each step manually:

```bash
# Build and start all services
docker-compose up -d

# View running containers
docker-compose ps

# View logs (useful for troubleshooting)
docker-compose logs -f

# View logs of specific container
docker-compose logs -f app
```

### Accessing the Application

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000

### VPS Deployment

To deploy on a VPS, follow these steps:

1. SSH into your VPS
2. Clone this repository
3. Navigate to the project directory
4. Run the deployment script:

```bash
./deploy.sh
```

5. Configure your web server (Nginx/Apache) to proxy requests to the appropriate Docker containers
6. Set up SSL using Let's Encrypt for secure connections

### Running Custom Commands

You can run custom commands inside the Docker containers:

```bash
# Run Laravel Artisan commands
docker-compose exec app php artisan list

# Run migrations manually
docker-compose exec app php artisan migrate:fresh --seed

# Run tenant migrations manually
docker-compose exec app php artisan tenants:migrate

# Run custom shell commands
docker-compose exec app bash

# Run NPM commands
docker-compose exec app npm run build
```

### Troubleshooting

If you encounter any issues:

1. Check the container logs:
```bash
docker-compose logs -f
```

2. Inspect the Laravel logs specifically:
```bash
docker-compose logs -f app
```

3. Check database connection:
```bash
docker-compose exec app php artisan db:monitor
```

4. Restart the services:
```bash
docker-compose restart
```

5. Rebuild the services:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

6. Check if all required Docker volumes are created:
```bash
docker volume ls | grep solutech
```
