#!/bin/bash
set -e

echo "Waiting for MariaDB at $DB_HOST:$DB_PORT..."
for i in {1..60}; do
  if mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASSWORD -e "SELECT 1" > /dev/null 2>&1; then
    echo "MariaDB is ready!"
    break
  fi
  echo "Attempt $i/60: MariaDB not ready yet..."
  sleep 2
done

echo "Running migrations..."
./vendor/bin/phinx migrate -e development

echo "Running seeds..."
./vendor/bin/phinx seed:run -e development || true

echo "Starting Apache..."
exec apache2-foreground
