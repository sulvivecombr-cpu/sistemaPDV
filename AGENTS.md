# AGENTS.md — Sistema PDV

## Stack
Vanilla PHP 8.2 + MySQL 8.4 + Composer (sped-nfe fiscal library). No framework, no build step — static PHP files served directly by PHP's built-in dev server.

## Running in Base44
`docker compose -f docker-compose.base44.yml up -d --build` starts three services:
- `db` — MySQL 8.4 (healthchecked)
- `setup` — one-shot: runs `composer install` then `.base44/setup.php` to create tables and seed a demo user
- `web` — PHP built-in server on port 3000, bind-mounted to the repo root for live edits

### Database
Credentials are generated locally in compose (`pdv` / `pdv_local_dev`). `config/db.php` and `conection.php` read them from env vars (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`). Schema is created idempotently by `.base44/setup.php` — there is no `.sql` migration file in the repo.

### Demo login
`admin@admin.com` / `admin` (seeded by setup.php on first run only).

### Routes
The app expects to live at the web root. `.base44/router.php` redirects `/` → `/login.php` and rewrites legacy `/pdv/...` links to root paths.

## Optional external credentials
Mercado Pago (`MP_ACCESS_TOKEN`, `MP_DEVICE_ID`) and product-image FTP (`FTP_HOST`, `FTP_USER`, `FTP_PASSWORD`) are read from environment. The app runs without them — payments fall back to manual mode and product images use a placeholder. Provide real values via the Base44 secrets dashboard to enable those integrations.

## Verifying
- Login page: `curl http://localhost:3000/login.php` should contain "Sistema PDV"
- Login flow: POST to `/actions/login_action.php` with `email=admin@admin.com&senha=admin` returns 302 to `/dash.php`
- All pages (`dash.php`, `estoque.php`, `pdv.php`, `modulo_de_vendas.php`, etc.) should load with no PHP fatal errors
