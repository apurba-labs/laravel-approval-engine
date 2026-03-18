## Quick Demo

```bash
git clone https://github.com/apurba-labs/laravel-approval-engine
cd approval-engine/example/laravel-demo

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan approval:demo
