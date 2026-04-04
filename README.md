<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

Applicazione **Sondaggi** su [Laravel](https://laravel.com/docs); sotto: riferimento operativo per Docker, porte e deploy VPS (allineato al piano stack tipo FitLifeMilano).

## Docker e deploy

### Prerequisiti

- **Docker Engine** e plugin **Docker Compose** (v2).
- File **`.env`** nella root del repository (copia da **`.env.example`**) con almeno `APP_KEY` (generabile con `php artisan key:generate` fuori da Docker se serve).

### Colima (senza Docker Desktop)

Su macOS o Linux puoi usare [Colima](https://github.com/abiosoft/colima) come backend Docker. Avvia la VM e poi gli stessi comandi `docker compose` della sezione sviluppo; i **bind mount** `./` funzionano per progetti sotto la directory home condivisa con la VM (comportamento predefinito).

Da terminale: `colima start`, poi `docker compose -f docker-compose.dev.yml -f docker-compose.yml up -d --build`.

In **Cursor / VS Code**: *Terminal › Run Task…* (o *Esegui attività*) e scegli **Sondaggi: Colima + dev stack (build + mount)** (`.vscode/tasks.json`: avvia Colima se serve, poi compose con build e mount dev). Per stack senza worker usa il task **…senza worker**.

### Porte e URL

L’HTTP dell’app nel container è sempre **`php artisan serve` sulla porta 10000** (interna al container).

| Stack | App (browser) | phpMyAdmin (loopback) | MySQL su host |
|--------|----------------|------------------------|---------------|
| `docker compose` solo file base | `http://127.0.0.1:8000` → **:10000** | nessuna porta pubblicata sul host (solo rete Docker) | `127.0.0.1:3306` |
| `docker-compose.dev.yml` + base | come sopra **e** `http://127.0.0.1:18080` → **:10000** | `http://127.0.0.1:8080` | come sopra |
| base + `docker-compose.prod.yml` | come base | `http://127.0.0.1:8084` | come sopra |

Healthcheck HTTP dell’app: **`GET /up`** sulla porta 10000 del container.

### Variabili d’ambiente essenziali

- **`MYSQL_*`** e coerenza con `DB_*` (vedi `.env.example`).
- **`APP_KEY`**, **`APP_URL`**, in produzione **`APP_ENV=production`**, **`APP_DEBUG=false`**, **`APP_RESPONSE_SALT`**.
- Con **`QUEUE_CONNECTION=database`**: nel `.env` impostare anche **`COMPOSE_PROFILES=queue`** così Compose avvia il servizio **worker**. Con **`sync`**, omettere `COMPOSE_PROFILES`.
- Overlay produzione: **`SONDAGGI_MEDIA_PATH`**, **`SONDAGGI_LOGS_PATH`** (opzionali; default in `docker-compose.prod.yml`).

### Sviluppo locale (bind mount + phpMyAdmin)

```bash
docker compose -f docker-compose.dev.yml -f docker-compose.yml up -d --build
```

In **`APP_ENV=production`**, l’entrypoint esegue `config:cache`, `route:cache`, `view:cache` (e `event:cache` se applicabile) prima di avviare `serve` / `migrate` / `worker`.

### Asset (Vite)

L’immagine Docker compila gli asset in **build** (`npm run build` nello stage Node); **`public/build`** è incluso nell’immagine.

Con il bind mount in dev, la cartella `public/build` dell’host può mascherare quella dell’immagine. Rigenerare gli asset con:

```bash
docker compose -f docker-compose.dev.yml -f docker-compose.yml --profile assets run --rm assets
```

oppure `npm ci && npm run build` sul host.

### Stack base e produzione (VPS)

Solo immagine applicativa e volumi named (nessun bind dell’intero repo):

```bash
docker compose up -d --build
```

Produzione con mount host per media e log (e phpMyAdmin su **8084**):

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

Sulla VPS creare directory dati e permessi (UID **www-data** su Alpine spesso **82**):

```bash
sudo mkdir -p /data/sondaggi/media /data/sondaggi/logs
sudo chown -R 82:82 /data/sondaggi/media /data/sondaggi/logs
```

Path del codice sul server consigliato per rsync/deploy: **`/opt/sondaggi`** (variabile **`DEPLOY_PATH`** in `deploy.env`).

### Database e migrazioni

Lo schema è definito **solo** dalle **migrations** Laravel. All’avvio dello stack, il servizio one-shot **`migrate`** esegue `php artisan migrate --force` dopo che MySQL è healthy; **`web`** e **`worker`** partono solo al termine con successo di **`migrate`**. Nessun DDL duplicato in `docker/mysql/init`.

### Traefik (opzionale)

Non è incluso in questo repository. In VPS si può attaccare un reverse proxy esterno (rete Docker dedicata, label TLS) puntando al servizio **`web`** sulla **porta interna 10000** (es. `loadbalancer.server.port=10000`).

### Worker vs coda `sync`

- **`QUEUE_CONNECTION=database`**: usare **`COMPOSE_PROFILES=queue`** nel `.env` per avviare il container **`worker`** (`queue:work`).
- **`QUEUE_CONNECTION=sync`**: non impostare `COMPOSE_PROFILES`; il servizio worker non deve essere attivo.

### Deploy da postazione locale (rsync)

1. `cp deploy.env.example deploy.env` e compilare almeno **`DEPLOY_HOST`** (path default **`DEPLOY_PATH=/opt/sondaggi`**).
2. `./scripts/deploy-vps.sh` oppure `./scripts/deploy-vps.sh --dry-run` per una prova a secco.

Lo script **non** esegue `docker compose down -v` e **non** deve usare `DEPLOY_PATH` sotto `/data/`. Sulla VPS, con coda database, il `.env` deve includere **`COMPOSE_PROFILES=queue`**.

### Log in produzione (persistenza)

Con `docker-compose.prod.yml`, i log Laravel sono sul disco host (default **`/data/sondaggi/logs`**). Restano tra un deploy e l’altro del codice sotto **`/opt/sondaggi`**.

#### Rotazione (`logrotate`)

Esempio `/etc/logrotate.d/sondaggi`:

```
/data/sondaggi/logs/*.log {
    weekly
    rotate 8
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
```

Con `LOG_CHANNEL=daily` adattare i glob; `copytruncate` aiuta se il processo tiene aperto il file.

### Checklist sicurezza (produzione)

- **`APP_ENV=production`**, **`APP_DEBUG=false`**, **`APP_KEY`** presente (mai in git).
- **`APP_RESPONSE_SALT`** impostato (hash IP / rate limit sondaggi pubblici).
- Permessi di scrittura per **`www-data`** su `storage` e `bootstrap/cache`; con bind **`/data/...`** allineare `chown` all’UID del container (spesso **82**).
- Segreti MySQL e `.env` applicativo solo sul server, non nel repository.

## Laravel (generico)

Documentazione framework: [laravel.com/docs](https://laravel.com/docs). Per segnalare vulnerabilità nel **framework** Laravel: [taylor@laravel.com](mailto:taylor@laravel.com).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
