#!/bin/bash

# Docker Utility Script for Solutech Project
# This script provides easy commands to manage the Docker environment

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to display help
function show_help {
  echo -e "${GREEN}Solutech Docker Utility Script${NC}"
  echo ""
  echo "Usage: ./docker-util.sh [command]"
  echo ""
  echo "Commands:"
  echo "  up              - Start all containers"
  echo "  down            - Stop all containers"
  echo "  restart         - Restart all containers"
  echo "  build           - Rebuild all containers without cache"
  echo "  logs            - View logs from all containers"
  echo "  logs:app        - View logs from Laravel app container"
  echo "  logs:frontend   - View logs from Nuxt frontend container"
  echo "  logs:db         - View logs from database container"
  echo "  migrate         - Run Laravel migrations"
  echo "  migrate:fresh   - Run fresh migrations with seeding"
  echo "  tenants:migrate - Run tenant migrations"
  echo "  bash:app        - Open bash shell in Laravel container"
  echo "  bash:frontend   - Open bash shell in Nuxt container"
  echo "  db:shell        - Open PostgreSQL shell"
  echo "  composer        - Run composer command (e.g. ./docker-util.sh composer require package-name)"
  echo "  artisan         - Run artisan command (e.g. ./docker-util.sh artisan make:controller)"
  echo "  npm:frontend    - Run npm command in frontend container"
  echo "  npm:app         - Run npm command in Laravel container"
  echo "  status          - Check status of containers"
}

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}docker-compose could not be found. Please install Docker and docker-compose.${NC}"
    exit 1
fi

# Execute command based on the first argument
case "$1" in
  up)
    echo -e "${GREEN}Starting all containers...${NC}"
    docker-compose up -d
    docker-compose ps
    ;;
  down)
    echo -e "${YELLOW}Stopping all containers...${NC}"
    docker-compose down
    ;;
  restart)
    echo -e "${YELLOW}Restarting all containers...${NC}"
    docker-compose restart
    docker-compose ps
    ;;
  build)
    echo -e "${YELLOW}Rebuilding all containers without cache...${NC}"
    docker-compose down
    docker-compose build --no-cache
    docker-compose up -d
    docker-compose ps
    ;;
  logs)
    echo -e "${GREEN}Viewing logs from all containers...${NC}"
    docker-compose logs -f
    ;;
  logs:app)
    echo -e "${GREEN}Viewing logs from Laravel app container...${NC}"
    docker-compose logs -f app
    ;;
  logs:frontend)
    echo -e "${GREEN}Viewing logs from Nuxt frontend container...${NC}"
    docker-compose logs -f frontend
    ;;
  logs:db)
    echo -e "${GREEN}Viewing logs from database container...${NC}"
    docker-compose logs -f db
    ;;
  migrate)
    echo -e "${GREEN}Running Laravel migrations...${NC}"
    docker-compose exec app php artisan migrate
    ;;
  migrate:fresh)
    echo -e "${YELLOW}Running fresh migrations with seeding...${NC}"
    docker-compose exec app php artisan migrate:fresh --seed
    ;;
  tenants:migrate)
    echo -e "${GREEN}Running tenant migrations...${NC}"
    docker-compose exec app php artisan tenants:migrate
    ;;
  bash:app)
    echo -e "${GREEN}Opening bash shell in Laravel container...${NC}"
    docker-compose exec app bash
    ;;
  bash:frontend)
    echo -e "${GREEN}Opening bash shell in Nuxt container...${NC}"
    docker-compose exec frontend sh
    ;;
  db:shell)
    echo -e "${GREEN}Opening PostgreSQL shell...${NC}"
    docker-compose exec db psql -U postgres -d events_central
    ;;
  composer)
    shift
    echo -e "${GREEN}Running composer command: $@${NC}"
    docker-compose exec app composer $@
    ;;
  artisan)
    shift
    echo -e "${GREEN}Running artisan command: $@${NC}"
    docker-compose exec app php artisan $@
    ;;
  npm:frontend)
    shift
    echo -e "${GREEN}Running npm command in frontend container: $@${NC}"
    docker-compose exec frontend npm $@
    ;;
  npm:app)
    shift
    echo -e "${GREEN}Running npm command in Laravel container: $@${NC}"
    docker-compose exec app npm $@
    ;;
  status)
    echo -e "${GREEN}Container status:${NC}"
    docker-compose ps
    ;;
  *)
    show_help
    ;;
esac
