## Dev Setup (Docker)

#### Prerequisites
- Docker

#### First time
```
docker-compose up --build
docker-compose exec app composer install
```

#### Every time
```
# Terminal 1
docker-compose up

# Terminal 2
npm install && npm run dev
```

Open http://localhost:8001

---

## Dev Setup (Linux)

#### Prerequisites
```
sudo apt install php8.0-cli php8.0-fpm php8.0-mbstring php8.0-xml php8.0-pgsql
```

#### First time
```
composer install
npm install
cp .env.example .env  # set DB creds
php artisan key:generate
php artisan migrate
```

#### Every time
```
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

#### Note
laravel.log permission error: `sudo chown www-data:www-data storage -R`
