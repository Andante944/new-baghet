# Baghet showcase

Laravel-витрина пластикового багета и фурнитуры. Исходный HTML-макет лежит в `mock/`.

## Запуск

```bash
php artisan migrate --seed
php artisan baghet:scrape
php artisan serve
```

Открыть http://127.0.0.1:8000

## Парсер baghet.md

Импортирует категории **пластик** (id 11) и **фурнитура** (id 16):

```bash
php artisan baghet:scrape
php artisan baghet:scrape --only=plastic
php artisan baghet:scrape --only=hardware --limit=20
php artisan baghet:scrape --no-images
```

Цены и остатки сейчас с PrestaShop; позже их заменит sync из Битрикс CRM (по `sku` / `bitrix_id`).
