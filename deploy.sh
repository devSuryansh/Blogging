#!/bin/bash

# Blog Platform Deployment Script
# This script sets up the production environment

set -e

echo "🚀 Setting up Blog Platform for production deployment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Create environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating environment configuration..."
    cat > .env << 'EOF'
# Application Configuration
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
APP_NAME="My Blog"

# Database Configuration
DB_HOST=db
DB_PORT=5432
DB_NAME=blog_db
DB_USER=blog_user
DB_PASSWORD=your_secure_password_here

# Redis Configuration
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password_here

# Security
APP_KEY=your_secret_key_here_make_it_32_chars_long
SESSION_LIFETIME=120

# Email Configuration (optional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@yourblog.com
MAIL_FROM_NAME="Your Blog"

# File Upload Configuration
MAX_UPLOAD_SIZE=5M
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx
EOF

    echo "⚠️  Please edit .env file with your actual configuration values!"
    echo "   Especially update DB_PASSWORD, REDIS_PASSWORD, and APP_KEY"
fi

# Create data directories
echo "📁 Creating data directories..."
mkdir -p data/postgres
mkdir -p data/redis
mkdir -p logs/nginx
mkdir -p logs/apache
mkdir -p uploads

# Set proper permissions
echo "🔒 Setting up permissions..."
chmod 755 data/postgres data/redis logs/nginx logs/apache uploads
chmod 600 .env

# Generate SSL certificates for development (self-signed)
if [ ! -f docker/nginx/ssl/nginx.crt ]; then
    echo "🔐 Generating SSL certificates for development..."
    mkdir -p docker/nginx/ssl
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/nginx.key \
        -out docker/nginx/ssl/nginx.crt \
        -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost" \
        2>/dev/null || echo "⚠️  OpenSSL not available, skipping SSL setup"
fi

# Pull and build images
echo "📦 Pulling and building Docker images..."
docker-compose pull
docker-compose build

# Start services
echo "🔄 Starting services..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Run database migrations
echo "🗄️  Running database migrations..."
docker-compose exec -T db psql -U $DB_USER -d $DB_NAME -f /docker-entrypoint-initdb.d/001_initial_schema.sql || true

# Check service health
echo "🏥 Checking service health..."
sleep 5

# Check if services are running
if docker-compose ps | grep -q "Up"; then
    echo "✅ Services are running!"
    echo ""
    echo "🌐 Your blog is now available at:"
    echo "   - Development: http://localhost:8080"
    echo "   - Production (with SSL): https://localhost:8443"
    echo ""
    echo "📊 Monitoring dashboards:"
    echo "   - Prometheus: http://localhost:9090 (production only)"
    echo "   - Grafana: http://localhost:3000 (production only)"
    echo ""
    echo "🔧 Useful commands:"
    echo "   - View logs: docker-compose logs -f"
    echo "   - Stop services: docker-compose down"
    echo "   - Restart services: docker-compose restart"
    echo "   - Access database: docker-compose exec db psql -U blog_user -d blog_db"
    echo ""
    echo "📝 Next steps:"
    echo "   1. Update .env file with your actual configuration"
    echo "   2. Configure your domain name in production"
    echo "   3. Set up proper SSL certificates for production"
    echo "   4. Configure email settings for notifications"
else
    echo "❌ Some services failed to start. Check logs with: docker-compose logs"
    exit 1
fi