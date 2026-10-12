# OSDP MIS Portal
### Odisha State Data Policy (OSDP) 2.0 Management Information System
**Capacity Building, Training & Evidence Management System**

---

## Overview

The **OSDP MIS Portal** is a production-oriented, secure web application designed and developed for the **Odisha Computer Application Centre (OCAC)** and the Government of Odisha. The portal tracks, monitors, verifies, and reports on the end-to-end execution of the Statewide Capacity Building and Citizen Awareness Programme under OSDP 2.0 across all 30 districts, 314 blocks, and Gram Panchayats of Odisha.

---

## Key Features

1. **Strict Role-Based Access Control & Geographic Scopes:**
   - 6 user roles: `Super Admin`, `OCAC`, `PMU / Programme Manager`, `District Coordinator`, `Field Trainer`, `Independent Field Monitor`.
   - Record-level policy isolation: District Coordinators are restricted to assigned districts; Field Trainers to assigned batches; Monitors to assigned visits; OCAC is read-only.
2. **Administrative Hierarchy & Master Data:**
   - Pre-seeded with all 30 Districts of Odisha, representative Blocks, Gram Panchayats, 4 Training Tiers, and Modules 1 to 8 from the official curriculum.
   - Dynamic cascading AJAX dropdowns (District $\rightarrow$ Blocks $\rightarrow$ Gram Panchayats).
3. **Training Batch Planning & Scheduling:**
   - Auto-generated deterministic Batch Codes (e.g. `OSDP-DIR-STA-0001`, `OSDP-BLK-CTC-0014`, `OSDP-GP-KHD-0042`).
   - Cluster batch mode support for multi-GP sessions.
   - Automated 48-hour submission deadline calculation.
4. **Form 2 Attendance Register Transcription:**
   - Side 1 and Side 2 transcription (Morning arrival sign and Afternoon post-lunch sign).
   - Real-time aggregation of registered and present participant headcounts.
5. **Private Evidence Storage & Verification (6 Mandatory Items):**
   - 1: Attendance sheet (Form 2)
   - 2: Photograph 1 (Full group with 8ft $\times$ 5ft banner)
   - 3: Photograph 2 (Trainer teaching)
   - 4: Video clip (Short MP4, ~1 min, landscape mode)
   - 5: Participant feedback bundle (Form 1 PDF)
   - 6: Batch completion report (Form 3 PDF & Part C Training Summary)
   - Files stored privately outside webroot with SHA-256 integrity hashes and authorized streaming.
6. **Two-Stage Review Workflow:**
   - District Coordinator physical pack completeness checks (`pack_checked`).
   - PMU central decision engine (`verified`, `correction_requested`, `rejected`).
7. **Independent Field Audits:**
   - On-site visit logs recording headcount, banner display, training kits distribution, refreshments, quality ratings, and observations.
8. **Dashboards & Regulatory Exports:**
   - Real-time aggregated metrics and Chart.js visualizations.
   - Comprehensive CSV spreadsheets and print-ready reports.
9. **DPDP Act 2023 Compliance & Security:**
   - Authentication rate limiting, session ID regeneration, CSRF validation, parameterized Eloquent queries, and immutable audit trails.

---

## Quick Start (Local Development)

### Prerequisites
- PHP 8.2 or higher (with `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`)
- Composer
- Node.js & NPM
- MySQL Server 8.0+

### Setup Commands
```bash
# 1. Clone & Enter Project
cd c:\xampp\htdocs\OSDP

# 2. Environment Configuration
cp .env.example .env
php artisan key:generate

# 3. Database Migration & Master Data Seeding
php artisan migrate --force
php artisan db:seed --force

# 4. Compile Assets
npm install
npm run build

# 5. Start Server
php artisan serve
```

---

## Demo Test Credentials (All Roles)

Password for all pre-seeded demo accounts: `Admin@12345`

| Role | Email Address | Access Scope |
|---|---|---|
| **Super Admin** | `admin@osdp.gov.in` | Complete system administration & user management |
| **OCAC Nodal Authority** | `ocac@osdp.gov.in` | Executive monitoring & audit oversight (Read-only) |
| **PMU Programme Manager** | `pmu@osdp.gov.in` | Central planning, batch reviews, and milestone approval |
| **District Coordinator (Khordha)** | `dc.khordha@osdp.gov.in` | Khordha district batches, pack checks, and attendance |
| **District Coordinator (Cuttack)** | `dc.cuttack@osdp.gov.in` | Cuttack district batches, pack checks, and attendance |
| **Master Field Trainer** | `trainer.mohanty@osdp.gov.in` | Assigned training batches, Form 2 attendance, evidence uploads |
| **Field Monitor (Auditor)** | `monitor.das@osdp.gov.in` | Independent field inspections and quality scoring |

---

## Documentation Index

- [Requirements Specification](docs/REQUIREMENTS.md)
- [Database Architecture & Schema](docs/DATABASE_DESIGN.md)
- [Role & Permission Matrix](docs/ROLE_PERMISSION_MATRIX.md)
- [Implementation Plan](docs/IMPLEMENTATION_PLAN.md)
- [Installation Guide](docs/INSTALLATION.md)
- [Production Deployment Guide](docs/DEPLOYMENT.md)
- [Application Security Guide](docs/SECURITY.md)
- [Backup and Disaster Recovery](docs/BACKUP_AND_RESTORE.md)
- [User Guide](docs/USER_GUIDE.md)
- [Automated Testing Strategy](docs/TESTING.md)
