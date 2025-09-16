# Blog Platform - Production Ready Deployment

A modern, responsive blogging platform built with PHP, PostgreSQL, and Docker. Features a beautiful Tailwind CSS interface and comprehensive security measures for production deployment.

## 🚀 Features

- **Modern UI**: Responsive design with Tailwind CSS
- **User Authentication**: Secure login/registration system
- **Content Management**: Rich text editor with auto-save
- **Search Functionality**: Full-text search across posts
- **Comment System**: Threaded comments with moderation
- **Like System**: User engagement features
- **Docker Ready**: Complete containerization for easy deployment
- **Production Security**: Security headers, rate limiting, and monitoring
- **Database Migrations**: Structured database schema management

## 📋 Prerequisites

- Docker and Docker Compose
- Git
- At least 2GB RAM for production deployment

## �️ Quick Start

### 1. Clone and Setup

```bash
git clone <your-repo-url>
cd Blogging
./deploy.sh
```

The deployment script will:

- Create necessary directories and permissions
- Generate environment configuration
- Set up SSL certificates (self-signed for development)
- Build and start all services
- Run database migrations

### 2. Configure Environment

Edit the `.env` file with your actual configuration:

```env
# Update these critical values
DB_PASSWORD=your_secure_database_password
REDIS_PASSWORD=your_redis_password
APP_KEY=your_32_character_secret_key_here

# For production, update the URL
APP_URL=https://yourdomain.com
```

### 3. Access Your Blog

- **Development**: <http://localhost:8080>
- **Production**: <https://localhost:8443>
- **Admin Dashboard**: Login with any registered user

## 🏗️ Architecture

### Services

- **App Container**: PHP 8.2 with Apache
- **Database**: PostgreSQL 16 with optimized configuration
- **Cache**: Redis for session management and caching
- **Proxy**: Nginx with security headers and rate limiting
- **Monitoring**: Prometheus and Grafana (production)
- **Logging**: Fluentd for log aggregation (production)

### File Structure

```md
├── config/ # Configuration files
│ ├── bootstrap.php # Application bootstrap
│ └── database.php # Database configuration
├── docker/ # Docker configuration
│ ├── apache/ # Apache virtual host
│ ├── nginx/ # Nginx proxy configuration
│ └── php/ # PHP configuration
├── migrations/ # Database schema files
├── uploads/ # File uploads directory
├── \*.php # Application files
├── docker-compose.yml # Development setup
├── docker-compose.prod.yml # Production with monitoring
└── deploy.sh # Automated deployment script
```

## 🔧 Development

### Running Locally

```bash
# Start development environment
docker-compose up -d

# View logs
docker-compose logs -f

# Access database
docker-compose exec db psql -U blog_user -d blog_db

# Run migrations
docker-compose exec app php -r "require 'migrations/001_initial_schema.sql';"
```

### Making Changes

1. Edit PHP files directly (changes are reflected immediately)
2. For configuration changes, restart containers:

   ```bash
   docker-compose restart
   ```

## 🚀 Production Deployment

### Option 1: VPS/Cloud Server

1. **Prepare Server**:

   ```bash
   # Install Docker
   curl -fsSL https://get.docker.com -o get-docker.sh
   sh get-docker.sh

   # Install Docker Compose
   sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   sudo chmod +x /usr/local/bin/docker-compose
   ```

2. **Deploy Application**:

   ```bash
   git clone <your-repo>
   cd Blogging

   # Edit .env for production
   nano .env

   # Deploy with monitoring
   docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
   ```

3. **Set up Domain**:
   - Point your domain to server IP
   - Update `APP_URL` in `.env`
   - Configure SSL certificates (Let's Encrypt recommended)

### Option 2: Cloud Platforms

#### DigitalOcean App Platform

```yaml
# app.yaml
name: blog-platform
services:
  - name: web
    source_dir: /
    github:
      repo: your-username/your-repo
      branch: main
    run_command: docker-compose up
    environment_slug: docker
    instance_count: 1
    instance_size_slug: basic-xxs
```

#### AWS ECS / Google Cloud Run

- Use the provided Dockerfile
- Set environment variables in cloud console
- Configure managed database services

### SSL Configuration

For production with Let's Encrypt:

```bash
# Install certbot
sudo apt install certbot

# Get certificates
sudo certbot certonly --standalone -d yourdomain.com

# Update nginx configuration to use real certificates
# Copy certificates to docker/nginx/ssl/
```

## 🔒 Security Features

- **CSRF Protection**: Built-in token validation
- **XSS Prevention**: Input sanitization and output escaping
- **SQL Injection Protection**: Prepared statements
- **Security Headers**: HSTS, CSP, X-Frame-Options
- **Rate Limiting**: Login attempt and API rate limiting
- **Session Security**: Secure cookie configuration
- **File Upload Security**: Type validation and size limits

## 📊 Monitoring

Production deployment includes:

- **Prometheus**: Metrics collection (<http://localhost:9090>)
- **Grafana**: Visualization dashboards (<http://localhost:3000>)
- **Log Aggregation**: Centralized logging with Fluentd
- **Health Checks**: Automated service health monitoring

## 🛠️ Maintenance

### Backup Database

```bash
# Create backup
docker-compose exec db pg_dump -U blog_user blog_db > backup.sql

# Restore backup
docker-compose exec -T db psql -U blog_user -d blog_db < backup.sql
```

### Update Application

```bash
# Pull latest changes
git pull

# Rebuild and restart
docker-compose build
docker-compose up -d
```

### Scale Services

```bash
# Scale application containers
docker-compose up -d --scale app=3
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**:

   ```bash
   # Check database is running
   docker-compose ps

   # Check logs
   docker-compose logs db
   ```

2. **Permission Denied**:

   ```bash
   # Fix permissions
   sudo chown -R www-data:www-data uploads/
   chmod 755 uploads/
   ```

3. **Memory Issues**:

   ```bash
   # Increase memory limit in docker/php/php.ini
   memory_limit = 256M
   ```

### Debug Mode

Enable debug mode for development:

```env
APP_ENV=development
APP_DEBUG=true
```

## � API Documentation

### Authentication Endpoints

- `POST /login.php` - User login
- `POST /register.php` - User registration
- `GET /logout.php` - User logout

### Content Endpoints

- `GET /` - Homepage with posts
- `GET /dashboard.php` - User dashboard
- `POST /editor.php` - Create/edit posts
- `POST /comment.php` - Add comments
- `POST /like.php` - Like/unlike posts

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## � License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For issues and questions:

1. Check the troubleshooting section
2. Review logs: `docker-compose logs`
3. Open an issue on GitHub
4. Check community discussions

## 🔄 Updates

Keep your deployment updated:

```bash
# Check for updates
git fetch origin

# Update to latest
git pull origin main
docker-compose pull
docker-compose up -d
```

---

## 🎯 Quick Deployment Summary

For immediate deployment:

```bash
# 1. Clone and deploy
git clone <repo> && cd Blogging && ./deploy.sh

# 2. Configure environment
nano .env

# 3. Access your blog
open http://localhost:8080
```

Your production-ready blog platform is now live! 🚀
