#!/bin/bash
set -e

# Wait for MariaDB to be ready
echo "Waiting for MariaDB at $DB_HOST:$DB_PORT..."
for i in {1..60}; do
  if mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASSWORD -e "SELECT 1" > /dev/null 2>&1; then
    echo "MariaDB is ready!"
    break
  fi
  echo "Attempt $i/60: MariaDB not ready yet..."
  sleep 2
done

# Run migrations
echo "Running migrations..."
./vendor/bin/phinx migrate -e development

# Run seeds
echo "Running seeds..."
./vendor/bin/phinx seed:run -e development || true

echo "Starting Apache..."
# Start Apache
exec apache2-foreground
