# BingoLive

Realtime live bingo — **Vue client** + **Laravel API**.

```
.
├── api/       Laravel 13 · PHP 8.5 · Sanctum · Reverb · Bingo engine
├── client/    Vue 3 · Vite · Pinia · Router · Echo
└── package.json
```

## Yêu cầu

- **PHP 8.5+** (khuyến nghị 8.5.12) — `php -v`
- Composer 2, Node 20+

macOS (Homebrew):

```bash
brew install php@8.5 composer
brew link php@8.5 --force --overwrite
```

Ubuntu:

```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.5-cli php8.5-mbstring php8.5-xml php8.5-curl \
  php8.5-sqlite3 php8.5-zip php8.5-bcmath php8.5-intl php8.5-gd
sudo update-alternatives --set php /usr/bin/php8.5
```

## Setup (một lần)

```bash
git clone <repo-url> BingoLive
cd BingoLive

# API
cd api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

# Client
cd ../client
npm install
cp .env.example .env
# Copy REVERB_APP_KEY từ api/.env → VITE_REVERB_APP_KEY
```

## Chạy (3 terminal)

```bash
cd api && php artisan reverb:start   # :8080
cd api && php artisan serve          # :8000
cd client && npm run dev             # :5173 → mở browser
```

## Demo

| Email | Password | Role |
|---|---|---|
| player@bingolive.local | password123 | USER |
| host@bingolive.local | password123 | HOST |
| admin@bingolive.local | password123 | ADMIN |

Không tiền thật — phòng miễn phí / phí điểm ảo.
