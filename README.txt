# Requirements
sudo apt install php8.0-cli php8.0-fpm php8.0-mbstring php8.0-xml php8.0-pgsql

# Setup
git clone https://github.com/flawiddsouza/Bookworm
composer install
npm install
php artisan key:generate
cp .env.example .env # set db creds
php artisan migrate

# To develop without docker
php artisan serve
npm run dev

# To develop with docker
./vendor/bin/sail up --build -d
./vendor/bin/sail npm run dev
npm run dev

# laravel.log permission denied error when running through nginx
sudo chown www-data:www-data storage -R
