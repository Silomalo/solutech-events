#!/bin/bash

# Stop any running containers
docker compose down

# Build the images
docker compose build --no-cache

# Start the containers
docker compose up -d

# Show running containers
docker compose ps
