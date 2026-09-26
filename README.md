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

Картинки и атрибуты — с PrestaShop (`baghet:scrape`). Имена и цены продажи
для багета — из **etichete-crm** (источник истины), матч по `sku`:

```bash
# .env: CRM_CATALOG_URL=https://…/api/bagheta/catalog
#       CRM_CATALOG_TOKEN=…
php artisan baghet:sync-crm-catalog
```

После привязки (`crm_product_id`) повторный scrape **не** перезаписывает
`name_*` и `price` — только картинки, остатки и прочие поля.
