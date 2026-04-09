## Dev Setup (Docker)

#### Prerequisites
- Docker

#### First time
```
docker-compose up --build
```

This setup now keeps `vendor`, `bootstrap/cache`, and `storage/framework` inside Docker-managed Linux volumes instead of the Windows bind mount. That cuts the biggest local PHP filesystem bottleneck on Docker Desktop, and the container auto-seeds `vendor` on first boot.

#### Every time
```
# Terminal 1
docker-compose up

# Terminal 2
npm install && npm run dev
```

Open http://localhost:8001

#### If the app suddenly feels slow again
```
docker-compose down
docker volume rm bookworm_bookworm-vendor bookworm_bookworm-bootstrap-cache bookworm_bookworm-storage-framework
docker-compose up --build
```

For the best Docker performance on Windows, keep the repo inside your WSL/Linux filesystem instead of a mounted drive like `G:\...` when possible. PHP routes touch a lot of files, and Windows bind mounts are much slower than static assets for that workload.

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
