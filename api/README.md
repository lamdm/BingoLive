# BingoLive — Laravel 13 + Vue 3 (Inertia)

Party bingo livestream demo (light UI):

- Phòng **miễn phí** & **phí điểm ảo**
- Live play + chat realtime (**Laravel Reverb**)
- Host control + Admin
- Livestream placeholder (chưa gắn provider thật)

## Yêu cầu

- PHP 8.3+
- Composer
- Node 20+

## Chạy local

```bash
cd ~/Documents/my-project   # hoặc /Documents/my-project
composer install
cp .env.example .env        # nếu chưa có
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build               # hoặc npm run dev
php artisan reverb:start    # terminal 1 — realtime
php artisan serve           # terminal 2 — http://127.0.0.1:8000
```

Dev frontend hot reload (terminal 3):

```bash
npm run dev
```

## Tài khoản demo

| Email | Password | Role |
|---|---|---|
| `player@bingolive.local` | `password123` | USER |
| `host@bingolive.local` | `password123` | HOST |
| `admin@bingolive.local` | `password123` | ADMIN |

## Stack

- Laravel **13** + Breeze + Inertia + Vue 3
- Laravel Reverb + Echo
- SQLite (dev)
- Tailwind light-party UI
