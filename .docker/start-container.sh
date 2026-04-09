#!/bin/sh
set -eu

APP_DIR="/var/www/html"
VENDOR_DIR="$APP_DIR/vendor"
SEED_VENDOR_DIR="/opt/bookworm/vendor"

if [ ! -f "$VENDOR_DIR/autoload.php" ]; then
    echo "Vendor volume is empty; seeding dependencies from image cache..."
    mkdir -p "$VENDOR_DIR"

    if [ -f "$SEED_VENDOR_DIR/autoload.php" ]; then
        cp -a "$SEED_VENDOR_DIR/." "$VENDOR_DIR/"
    else
        composer install --working-dir="$APP_DIR" --no-interaction --prefer-dist
    fi
fi

mkdir -p \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/storage/framework/cache" \
    "$APP_DIR/storage/framework/cache/data" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/testing" \
    "$APP_DIR/storage/framework/views"

chown -R www-data:www-data \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/storage/framework"

chmod -R ug+rwx \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/storage/framework"

exec apache2-foreground
