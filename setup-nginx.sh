#!/bin/bash

# This script sets up Nginx as a reverse proxy for your Docker containers on a VPS

# Check if script is run as root
if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root" 1>&2
   exit 1
fi

# Install Nginx if not already installed
if ! command -v nginx &> /dev/null; then
    apt update
    apt install -y nginx
fi

# Create Nginx configuration for reverse proxy
cat > /etc/nginx/sites-available/solutech-app << 'EOL'
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 80;
    server_name api.yourdomain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOL

# Enable the site
ln -sf /etc/nginx/sites-available/solutech-app /etc/nginx/sites-enabled/

# Test Nginx configuration
nginx -t

# Reload Nginx to apply changes
systemctl reload nginx

echo "Nginx reverse proxy has been set up."
echo "Please replace 'yourdomain.com' with your actual domain in /etc/nginx/sites-available/solutech-app"
echo "Then run: certbot --nginx -d yourdomain.com -d www.yourdomain.com -d api.yourdomain.com"
echo "to secure your site with SSL."
