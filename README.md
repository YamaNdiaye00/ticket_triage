# Ticket Triage System

## Project Description
The **Ticket Triage System** is a production-style **Laravel 11 + Vue 3 SPA** designed to help a help-desk team manage incoming support tickets efficiently.

**Purpose**  
- Automate ticket classification using AI (OpenAI API), reducing manual workload.  
- Provide a backend API with CRUD operations, classification, and stats for frontend dashboards.  
- Support manual overrides while still giving AI-powered confidence and explanations.  
- Provide a modern, responsive Vue 3 frontend for staff to triage tickets quickly.

**Functionality**
- **Tickets CRUD**: Create, update, list, and view support tickets with search, filtering, pagination, and input validations.
- **AI-driven classification**: Categorizes tickets into `Billing`, `Technical`, `Account`, or `Other`, with explanation and confidence.
- **Manual override**: If staff changes category, AI will not overwrite it, but will still add explanation and confidence.
- **Bulk classify**: Artisan command to classify all tickets missing AI data (`--only-missing`).
- **Stats endpoint**: Provides counts for dashboard charts.
- **Dashboard & charts**: Ticket counts by status & category with a simple Chart.js chart.
- **CSV export**: Modal with filters (download support in progress).
- **Dark/Light theme toggle**: Project-wide via CSS variables.
- **Rate limiting**: Protects `/stats` (30 requests/min) and `/classify` (10 requests/min) from abuse.

**Effectiveness**  
- Reduces manual triage effort by auto-classifying routine tickets.
- Provides transparency with explanations and confidence scores.
- Staff can override AI and add notes cleanly.
- Includes safe fallbacks (random dummy results when AI disabled).
- Clean separation of concerns (services, jobs, routes, Vue frontend).
- Lightweight, maintainable architecture (Options API, plain CSS with BEM).

---

# Requirements

## Runtime
- **PHP 8.2+**
- **Laravel 11**
- **Composer**
- **Node.js 18+** (for Vite / frontend build)
- **Database**: MySQL (preferred), or MariaDB  
  - SQLite supported for quick development  
  - PostgreSQL or SQL Server possible if extensions installed
- **Queue backend**: `sync` for development; `database` or Redis for async jobs
- **Package managers**: `npm` (frontend), `composer` (backend)

---

## PHP / php.ini configuration

### Must-have extensions
```ini
extension=mbstring
extension=openssl
extension=pdo_mysql   ; or pdo_pgsql/sqlsrv depending on DB
extension=pdo_sqlite  ; optional, for SQLite/testing
extension=tokenizer
extension=curl
extension=fileinfo
extension=ctype
extension=bcmath
extension=json
extension=xml
```

### Good-to-have extensions
```ini
extension=intl       ; for string/locale handling
extension=gd         ; for image processing
extension=zip        ; for archives / composer
extension=dom        ; XML/HTML parsing
extension=exif       ; handy for file uploads
```

### TLS / CA bundle (required for OpenAI)
To make HTTPS requests to `api.openai.com`, PHP’s cURL/openssl must trust a certificate authority bundle:

1. Download the latest [cacert.pem](https://curl.se/docs/caextract.html).  
2. Save it in your PHP installation (e.g., `C:\PHP\8.2.29\extras\ssl\cacert.pem`).  
3. In your `php.ini`, set:
   ```ini
   curl.cainfo = "C:\PHP\8.2.29\extras\ssl\cacert.pem"
   openssl.cafile = "C:\PHP\8.2.29\extras\ssl\cacert.pem"
   ```
4. Restart your terminal/web server.  
5. Verify with:
   ```bash
   php -i | findstr /I "cainfo"
   php -i | findstr /I "cafile"
   ```

---

## Laravel (Composer dependencies)
- `laravel/framework:^11.0` (core)
- `openai-php/laravel` (AI integration)

---

## Frontend (NPM dependencies)
- **Vue 3 (Options API)**
- **Vite** (bundler)
- `axios` (API calls)
- `vue-router` (routing)
- `chart.js` (charts)  
> No CSS frameworks (plain CSS with **BEM** conventions only).

---

# Environment (.env.example)
```env
# App
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Database (MySQL default)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_triage
DB_USERNAME=root
DB_PASSWORD=

# Queue
QUEUE_CONNECTION=sync   # use database/redis in prod

# Cache/Session
CACHE_DRIVER=file
SESSION_DRIVER=file

# OpenAI
OPENAI_API_KEY=sk-xxxxx
OPENAI_CLASSIFY_ENABLED=true
OPENAI_CLASSIFY_MODEL=gpt-4o-mini

```

---

# Setup (≤10 steps)

1. Clone repo:
   ```bash
   git clone https://github.com/YamaNdiaye00/ticket_triage.git && cd ticket_triage
   ```
2. Copy env file:
   ```bash
   cp .env.example .env
   ```
   Fill DB and OpenAI values.
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Generate app key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations & seeders:
   ```bash
   php artisan migrate --seed
   ```
6. Install frontend dependencies:
   ```bash
   npm install
   ```
7. Build or run frontend:
   ```bash
   npm run dev   # for local dev
   npm run build # for production
   ```
8. Serve Laravel backend:
   ```bash
   php artisan serve
   ```
9. Access app:
    - Visit `http://127.0.0.1:8000/tickets` → Ticket list UI
    - Visit `http://127.0.0.1:8000/dashboard` → Dashboard with stats and chart
    - API endpoints also available under `/api/*`
10. (Optional) Bulk classify missing:
   ```bash
   php artisan tickets:bulk-classify --only-missing
   ```

---

# Notes
- After updating `.env`, always run:
  ```bash
  php artisan config:clear && php artisan cache:clear
  ```
- For classification:
  - If `OPENAI_CLASSIFY_ENABLED=true` → uses OpenAI API.  
  - If `OPENAI_CLASSIFY_ENABLED=false` → generates random category + dummy explanation/confidence.  
- `ClassifyTicket` job respects manual category overrides.  
- `/stats` limited to **30 req/min**, `/tickets/{id}/classify` limited to **10 req/min**.  
- **Frontend**:
    - Built with Vue 3 Options API.
    - CSS is BEM-compliant and organized per feature (ticket-list, ticket-show, dashboard, modal, etc).
    - Single Vite build, served from Laravel’s `/public/spa` with proper fallback route.  
