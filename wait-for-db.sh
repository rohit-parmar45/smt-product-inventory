#!/bin/sh
set -e

echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."

# Loop until MySQL accepts connections
until mysqladmin ping -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --ssl-mode=DISABLED >/dev/null 2>&1; do
  echo "MySQL is not ready yet..."
  sleep 3
done

echo "MySQL is ready! Starting Laravel..."
exec "$@"