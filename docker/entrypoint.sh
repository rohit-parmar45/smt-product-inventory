#!/bin/sh
set -e

echo "🚀 Starting Laravel application..."

# -----------------------------------------------
# Wait for MySQL to be ready
# -----------------------------------------------
echo "⏳ Waiting for MySQL..."
max_tries=30
counter=0
until mysqladmin ping -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    counter=$((counter + 1))
    if [ $counter -ge $max_tries ]; then
        echo "❌ MySQL did not become ready in time. Exiting."
        exit 1
    fi
    echo "   Attempt $counter/$max_tries - MySQL not ready yet..."
    sleep 2
done
echo "✅ MySQL is ready!"

# -----------------------------------------------
# Generate JWT secret if not set
# -----------------------------------------------
if [ -z "$JWT_SECRET" ] || [ "$JWT_SECRET" = "" ]; then
    echo "🔑 Generating JWT secret..."
    php artisan jwt:secret --force --no-interaction
fi

# -----------------------------------------------
# Run migrations
# -----------------------------------------------
echo "📦 Running database migrations..."
php artisan migrate --force

# -----------------------------------------------
# Seed database (only if users table is empty)
# -----------------------------------------------
echo "🌱 Checking if seeding is needed..."
USERS_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USERS_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
else
    echo "✅ Database already seeded (${USERS_COUNT} users found)."
fi

# -----------------------------------------------
# Cache configuration for performance
# -----------------------------------------------
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# -----------------------------------------------
# Ensure storage directories are writable
# -----------------------------------------------
echo "📁 Setting storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Application is ready! Starting PHP-FPM..."

# Hand off to CMD (php-fpm)
exec "$@"
