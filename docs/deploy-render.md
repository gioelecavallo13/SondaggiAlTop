# Deploy su Render (Laravel in `Sondaggi/`)

## Prerequisiti

- Database MySQL gestito (Render PostgreSQL non è compatibile con lo schema attuale senza adattamenti; usare **MySQL** o compatibile).
- Variabili ambiente coerenti con [`.env.example`](../.env.example).

## Variabili ambiente principali

| Chiave | Note |
|--------|------|
| `APP_KEY` | `php artisan key:generate` |
| `APP_URL` | URL pubblico del servizio (https) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_RESPONSE_SALT` | Stringa lunga casuale (hash IP / rate limit) |
| `DB_*` | Connessione MySQL |
| `SESSION_DRIVER` | `database` (richiede tabella `sessions` già nelle migration) |

## Build e comando di avvio (esempio)

Root del servizio: cartella **`Sondaggi/`** (non la root del monorepo).

1. Installazione PHP e Node (versione Node compatibile con Vite 8).
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci` e `npm run build` (genera `public/build/manifest.json`).
4. `php artisan migrate --force`
5. Avvio: `php artisan serve --host=0.0.0.0 --port=$PORT` oppure PHP-FPM + Nginx con `public/` come document root.

## Blueprint

Il [`render.yaml`](../../render.yaml) in root del repo usa `rootDir: Sondaggi` e `dockerfilePath: ./Dockerfile` (file in questa cartella). Impostare le variabili `APP_KEY`, `APP_URL`, database e `APP_RESPONSE_SALT`.

## Health check

Usare la route di default Laravel: `GET /up` (già registrata in `bootstrap/app.php`).
