# OSDP MIS Portal - Implementation Plan
**Odisha State Data Policy 2.0 Management Information System**

---

## 1. Technical Environment Baseline

- **PHP Version:** 8.2+ (verified CLI PHP active)
- **Framework:** Laravel 12.0
- **Database:** MySQL on 127.0.0.1:3306 (`osdp_db`)
- **Frontend Stack:** Laravel Blade templates, Tailwind CSS (Vite), Alpine.js / Vanilla JS, Chart.js
- **File Storage:** Private filesystem disk (`storage/app/private/evidence`)
- **Starter Kit / Auth:** Custom secure Blade authentication with throttling, session regeneration, password reset, and Spatie Laravel Permission.

---

## 2. Multi-Phase Implementation Roadmap

### Phase 1: Requirements & Architecture Discovery [COMPLETED]
- [x] Inspect project workspace and locate project documents (`RFP_OSDP_OCAC.pdf`, `OSDP_MIS_Forms_Pack.pdf`, `OSDP_MIS_Upload_Checklist.docx`).
- [x] Extract core specifications, form structures, and workflow requirements.
- [x] Author `docs/REQUIREMENTS.md`, `docs/DATABASE_DESIGN.md`, `docs/ROLE_PERMISSION_MATRIX.md`, and `docs/IMPLEMENTATION_PLAN.md`.

### Phase 2: Foundation, Authentication & Security Configuration
- [ ] Install Spatie Permission and supporting packages.
- [ ] Set up secure environment config (`.env`, `.env.example`, private filesystem disk).
- [ ] Implement Blade authentication: Login, Logout, Forgot Password, Reset Password, Profile, and Password Change.
- [ ] Implement rate limiting, session security, and account status middleware.

### Phase 3: RBAC & Record-Level Authorization
- [ ] Create role and permission definitions.
- [ ] Implement seeders for standard roles (Super Admin, OCAC, PMU, District Coordinator, Field Trainer, Field Monitor).
- [ ] Implement Laravel Policies for Batch, Evidence, Participant, Attendance, Review, and Verification models with geographic data scopes.

### Phase 4: Database Schema, Models & Seeders
- [ ] Create migrations for Master Data: Districts, Blocks, Gram Panchayats, Tiers, Modules, Venues.
- [ ] Create migrations for Training Plans, Batches, Batch Trainers, Cluster GPs, Batch Modules.
- [ ] Create migrations for Participants, Batch Participants, Attendance Records (Form 2).
- [ ] Create migrations for Training Uploads (Evidence Pack), Batch Summaries (Form 3), Reviews, Field Verifications, Audit Logs, and Settings.
- [ ] Create Eloquent models with relationships, casts, and query scopes.
- [ ] Seed master data (30 Odisha districts, sample blocks/GPs, 4 training tiers, 8 curriculum modules, sample users for each role).

### Phase 5: Master Data, Planning & Batch Scheduling
- [ ] Build controllers, requests, and views for Districts, Blocks, Gram Panchayats, and Venues.
- [ ] Implement dependent cascading AJAX endpoints (District $\rightarrow$ Blocks $\rightarrow$ GPs).
- [ ] Implement Training Plan management.
- [ ] Implement Training Batch CRUD with batch code generator, trainer assignments, tier validation, and status lifecycle state machine.

### Phase 6: Participant Registration, Attendance & Evidence Uploads
- [ ] Implement Participant directory and batch roster management.
- [ ] Implement Form 2 Attendance transcription (morning/afternoon signatures, registered vs present counts).
- [ ] Implement Evidence Upload center:
  - Six evidence item trackers (Form 2 Attendance, Photo 1, Photo 2, Video clip, Form 1 Feedback PDF, Form 3 Completion PDF).
  - Private filesystem storage, SHA-256 integrity hash, strict MIME type validation.
  - Secure streaming controllers with authorization checks.
  - 48-hour submission deadline calculator and SLA countdown indicators.

### Phase 7: Review Engine, Resubmission & Independent Field Verification
- [ ] District Coordinator completeness review checklist.
- [ ] PMU review decision workflow (`Approve`, `Request Correction`, `Reject`).
- [ ] Resubmission handling preserving complete audit history.
- [ ] Independent Field Monitor verification module (on-site headcount, banner, kits, quality rating).

### Phase 8: Role-Specific Dashboards & Analytical Reporting
- [ ] Build role-specific executive dashboards:
  - OCAC Dashboard (Statewide progress, tier matrix, feedback evaluation, read-only audit).
  - PMU Dashboard (Operational review queue, overdue batches, district league table).
  - District Coordinator Dashboard (District calendar, missing evidence, upcoming sessions).
  - Field Trainer Dashboard (Assigned batches, evidence submission checklist).
  - Field Monitor Dashboard (Assigned visits, pending verifications).
- [ ] Integrate Chart.js visual charts.
- [ ] Build exportable reports (Statewide Progress, District Breakdown, Attendance Master, Evidence Audit, Overdue Defaulters, CSV/Excel/PDF).

### Phase 9: Audit Trail, Operational Settings & Polish
- [ ] Build Audit Logging listener/service to capture model changes, file downloads, and security events.
- [ ] System settings management (deadline hours, file size caps, notification toggles).
- [ ] Reusable Blade component styling adhering to OSDP government design standards (Navy blue, white, saffron/teal accents).

### Phase 10: Automated Testing, Security Review & Deployment Documentation
- [ ] Write Feature and Unit tests covering:
  - Authentication & throttling.
  - Geographic record-level authorization and privilege escalation prevention.
  - Batch creation and lifecycle state machine.
  - Evidence file security, MIME validation, and private download protection.
  - Review decisions and SLA calculation.
- [ ] Run test suite and resolve any defects.
- [ ] Author operational guides: `README.md`, `docs/INSTALLATION.md`, `docs/DEPLOYMENT.md`, `docs/SECURITY.md`, `docs/USER_GUIDE.md`.
