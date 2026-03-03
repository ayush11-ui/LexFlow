# LexFlow
## Intelligent Differentiated Case Flow Management System (DCFM)

LexFlow is a production-oriented full-stack platform designed for modern courts to implement **Differentiated Case Flow Management** with intelligent automation.

It classifies cases into tracks, computes dynamic priority scores, and optimizes hearing schedules based on judge specialization, availability, and conflict constraints.

---

## Why LexFlow?

Traditional court workflows often rely on manual triaging and scheduling, causing avoidable backlog growth.

LexFlow addresses this with:

- **Automated case classification** (`fast`, `standard`, `complex`)
- **Dynamic priority scoring** based on urgency, age, and track weight
- **Intelligent scheduling** with conflict prevention
- **Role-aware workflows** for `admin`, `clerk`, and `judge`
- **Real-time operational insights** through dashboards and analytics

---

## System Architecture

### Backend
- **Laravel 11** (API-first)
- **PostgreSQL**
- **Laravel Sanctum** authentication
- Clean architecture layering:
  - `Controllers` → request orchestration
  - `Services` → business intelligence
  - `Repositories` → data access abstraction
  - `Policies + Middleware` → access control
  - `Resources` → consistent API JSON responses

### Frontend
- **React + Vite**
- **Tailwind CSS**
- **ShadCN-style UI primitives**
- **Recharts** (analytics visualizations)
- **FullCalendar** (schedule planning)
- **Framer Motion** (micro-interactions)

---

## Repository Structure

```text
LexFlow/
├─ backend/
│  ├─ app/
│  │  ├─ Console/Commands/
│  │  ├─ Http/
│  │  │  ├─ Controllers/
│  │  │  ├─ Middleware/
│  │  │  ├─ Requests/
│  │  │  └─ Resources/
│  │  ├─ Models/
│  │  ├─ Policies/
│  │  ├─ Providers/
│  │  ├─ Repositories/
│  │  └─ Services/
│  ├─ database/
│  │  ├─ factories/
│  │  ├─ migrations/
│  │  └─ seeders/
│  ├─ docs/openapi.yaml
│  ├─ routes/
│  └─ tests/Feature/
├─ frontend/
│  ├─ src/
│  │  ├─ components/
│  │  ├─ context/
│  │  ├─ lib/
│  │  ├─ pages/
│  │  └─ test/
│  ├─ vite.config.js
│  └─ tailwind.config.js
└─ .github/workflows/lexflow-ci.yml
```

---

## Roles & Access Model

### Admin
- Full platform access
- Manage users and scheduling
- Trigger auto-scheduling
- Access all analytics

### Clerk
- Create and view cases
- Use classification preview
- Cannot modify scheduling allocation

### Judge
- View assigned cases
- Update case status (within policy bounds)
- View schedule events

---

## Core Intelligence

### 1) Case Classification
Default logic:

- `urgency_level >= 4 && estimated_duration < 2` → `fast`
- `estimated_duration > 4` → `complex`
- else → `standard`

Override support:

- Rule-based overrides from `track_rules` table

### 2) Priority Scoring
Stored in DB and recalculated daily:

```text
priority_score =
(urgency_level * 0.4) +
(days_since_creation * 0.3) +
(track_weight * 0.3)
```

Track weights:
- `fast = 3`
- `standard = 2`
- `complex = 1`

### 3) Scheduling Engine
- Picks highest-priority unscheduled case
- Prefers specialization match
- Checks judge availability
- Checks courtroom availability
- Detects and blocks overlap conflicts
- Writes hearing + updates case state atomically

---

## API Surface (Highlights)

Authentication:
- `POST /api/login`
- `POST /api/logout`
- `POST /api/register` (admin only)
- `GET /api/me`

Cases:
- `GET /api/cases`
- `POST /api/cases`
- `POST /api/cases/classify/preview`
- `GET /api/cases/{id}`
- `PUT /api/cases/{id}`
- `DELETE /api/cases/{id}`

Scheduling:
- `POST /api/schedule/auto`
- `POST /api/schedule/manual`
- `GET /api/schedule/events`

Judges:
- `GET /api/judges`
- `GET /api/judges/{id}`
- `PUT /api/judges/{id}/availability`

Analytics:
- `GET /api/analytics/overview`
- `GET /api/analytics/backlog`
- `GET /api/analytics/workload`

Full OpenAPI spec:
- `backend/docs/openapi.yaml`

---

## Frontend UX Modules

- **Landing**: modern hero + platform stats
- **Login**: role-aware secure access
- **Dashboard**: KPI cards + line/bar/pie charts
- **Case Intake**: live track + priority preview
- **Case Queue**: filtered operational case table
- **Scheduling**: FullCalendar with auto-schedule control
- **Judges**: utilization cards + specialization snapshot
- **Analytics**: disposal time, aging, distribution views

---

## Local Development Setup

## Prerequisites

- PHP 8.2+ (8.3 recommended)
- Composer 2+
- Node.js 20+
- PostgreSQL 14+

## 1) Backend

```powershell
cd "C:\Users\AYUSH KUMAR\LexFlow\backend"
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Update `backend/.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lexflow
DB_USERNAME=postgres
DB_PASSWORD=your_password
FRONTEND_URL=http://localhost:5173
```

Run migrations and seed:

```powershell
php artisan migrate --seed
php artisan serve
```

Backend URL:
- `http://127.0.0.1:8000`

## 2) Frontend

```powershell
cd "C:\Users\AYUSH KUMAR\LexFlow\frontend"
npm install
Copy-Item .env.example .env
npm run dev
```

Frontend URL:
- `http://localhost:5173`

---

## Seeded Demo Accounts

- `admin@lexflow.local` / `Admin@12345`
- `clerk@lexflow.local` / `Clerk@12345`
- `judge.civil@lexflow.local` / `Judge@12345`

---

## Testing

### Backend (Laravel Feature Tests)

```powershell
cd "C:\Users\AYUSH KUMAR\LexFlow\backend"
php artisan test
```

Coverage includes:
- auth login/register authorization
- case creation & role filtering
- manual scheduling conflict checks

### Frontend (Vitest + RTL Smoke Tests)

```powershell
cd "C:\Users\AYUSH KUMAR\LexFlow\frontend"
npm run test
```

Coverage includes:
- landing rendering
- login submission flow
- protected route redirect

---

## CI/CD

GitHub Actions workflow:
- `.github/workflows/lexflow-ci.yml`

Pipeline jobs:
- Backend tests (Laravel)
- Frontend build + tests
- OpenAPI linting (`redocly`)

---

## Security Posture

- Request validation via Form Requests
- Role middleware + policy-based authorization
- Sanctum token authentication
- Controlled sorting/filtering at repository level
- SQL injection risk minimized through query builder usage
- Structured JSON resources to avoid accidental payload leakage

---

## Production Notes

Recommended production stack:
- **Nginx** + **PHP-FPM**
- **PostgreSQL managed instance**
- **Queue worker** for future async tasks
- **Scheduler** (`php artisan schedule:run`) for daily priority recalculation

Useful commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Roadmap Ideas

- Judge-side calendar drag-and-drop persistence
- Multi-courtroom configuration module
- File/document evidence management
- Audit log with immutable event history
- SLA breach forecasting with ML model scoring
- WhatsApp/SMS/email hearing reminders

---

## Contributing

1. Create a branch from `main`
2. Make focused changes with tests
3. Run backend + frontend test suites
4. Open PR with summary and screenshots (for UI changes)

---

## License

This project is currently unlicensed for private development use. Add an explicit OSS/commercial license before external distribution.

---

Built for modern judiciary operations with clarity, fairness, and flow efficiency.
