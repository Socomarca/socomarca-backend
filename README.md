## Setup

Setup environment configuration
```bash
cp .env.example .env
```

Build docker composition
```bash
docker compose build --build-arg USER_ID=$(id -u) --build-arg GROUP_ID=$(id -g) --no-cache
```

Start docker composition
```bash
docker compose up -d
```

Setup app encryption key
```bash
docker compose exec workcontainer php artisan key:generate
```

Install composer dependencies
```bash
docker compose exec workcontainer composer install
```

## Commands

Get into php container
```bash
docker compose exec -it workcontainer bash
```


## Sync products

*Order is important, categories must be synced before products.*

Start queue worker
```bash
docker compose exec workcontainer php artisan queue:work
```

Sync categories
```bash
docker compose exec workcontainer php artisan random:sync-categories
```

Sync products
```bash
docker compose exec workcontainer php artisan random:sync-products
```


Sync prices
```bash
docker compose exec workcontainer php artisan random:sync-prices
```
# Testing

First you must create a testing DB
```sql
CREATE DATABASE socomarca_backend_testing;
```

Then run the migrations in the testing database
```bash
php artisan migrate --env=testing
```

Finally you will be able to run all the tests
```bash
php artisan test --env=testing
```
