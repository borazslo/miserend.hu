#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${MYSQL_DATABASE:-miserend}"
DATA_DIR="/docker-entrypoint-initdb.d/data"

# Detect client binary: prefer mariadb, fallback to mysql
if command -v mariadb >/dev/null 2>&1; then
  DB_CLIENT="mariadb"
elif command -v mysql >/dev/null 2>&1; then
  DB_CLIENT="mysql"
else
  echo "Error: Neither 'mariadb' nor 'mysql' client found!" >&2
  exit 1
fi

MYSQL_CMD="$DB_CLIENT --user=root --password=pw ${DB_NAME}"

echo "Using client: $DB_CLIENT"

for file in "$DATA_DIR"/*.sql; do
  table=$(basename "$file" .sql)

  # Skip if table does not exist yet
  exists=$($MYSQL_CMD -N -s -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}' AND table_name='${table}';")
  if [ "$exists" -eq 0 ]; then
    echo "Skipping $table: table does not exist yet"
    continue
  fi

  # Check if table has rows
  rows=$($MYSQL_CMD -N -s -e "SELECT COUNT(*) FROM \`${table}\`;")
  if [ "$rows" -eq 0 ]; then
    echo "Importing data for table $table..."
    $MYSQL_CMD < "$file"
  else
    echo "Skipping $table: already has $rows rows"
  fi
done
