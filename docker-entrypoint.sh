#!/bin/bash

# Zero-configuration setup script for Hoist PHP Framework
echo "🚀 Starting Hoist PHP Framework..."

# Create .env from .env.example if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    echo "✅ Created .env from .env.example for zero-configuration setup"
else
    echo "✅ .env file already exists"
fi

# Install Composer dependencies if vendor directory doesn't exist or is incomplete
if [ ! -d /var/www/html/vendor ] || [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "📦 Installing Composer dependencies..."
    cd /var/www/html
    composer install --no-dev --optimize-autoloader --no-interaction
    echo "✅ Composer dependencies installed"
else
    echo "✅ Composer dependencies already installed"
fi

# Ensure proper permissions for FileDatabase and uploads
chown -R www-data:www-data /var/www/html/Application/Database 2>/dev/null || true
chmod -R 755 /var/www/html/Application/Database 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/uploads 2>/dev/null || true
chmod -R 755 /var/www/html/public/uploads 2>/dev/null || true
echo "✅ Set permissions for FileDatabase and uploads directories"

echo "🌟 Hoist PHP Framework ready! Visit http://localhost"

# Start Apache in foreground
exec apache2-foreground
