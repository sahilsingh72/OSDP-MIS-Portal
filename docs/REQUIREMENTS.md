# OSDP MIS Portal - Requirements Specification
**Odisha State Data Policy (OSDP) 2.0 Capacity Building, Training & Awareness Programme**
*Management Information System (MIS) for Training, Monitoring & Evidence Management*

---

## 1. Executive Summary & Context

Under the Odisha State Data Policy (OSDP) 2.0, the Odisha Computer Application Centre (OCAC) mandates the execution of a comprehensive Statewide capacity-building and citizen awareness programme across all 30 districts of Odisha. 

The **OSDP MIS Portal** is the centralized, secure, web-based management information system designed to plan, schedule, track, monitor, verify, and report on all training batches, participant registrations, attendance records, multimedia evidence items, field verifications, and quality metrics.

The application adheres to the guidelines outlined in:
1. **RFP for Selection of Service Provider for Capacity Building, Training and Awareness on OSDP 2.0** (`docs/RFP_OSDP_OCAC.pdf`)
2. **OSDP MIS Forms Pack** (`docs/OSDP_MIS_Forms_Pack.pdf`)
3. **OSDP MIS Upload Checklist** (`docs/OSDP_MIS_Upload_Checklist.docx`)
4. **Digital Personal Data Protection (DPDP) Act, 2023** and Government of Odisha IT Security Guidelines.

---

## 2. Administrative Hierarchy & Training Tiers

### 2.1 Administrative Structure
The administrative hierarchy in Odisha comprises:
$$\text{State (Odisha)} \longrightarrow \text{Districts (30)} \longrightarrow \text{Blocks (314)} \longrightarrow \text{Gram Panchayats (6,798+)}$$

### 2.2 Training Tiers & Volume
As defined in the RFP and Upload Checklist:

| Tier | Target Audience | Indicative Batches | Target Participants / Batch | Indicative Total Participants | Duration & Mode |
|---|---|---|---|---|---|
| **Tier 1: Directorate** | Governance Officials, Senior Dept Officers (below Asst. Director) | ~100 | ~30 | 3,000 | 1 Day (7–8 hrs), Physical |
| **Tier 2: District** | District Collectors, ADMs, DPOs, District Data Coordinators, IT Officers | 60 | ~40 | 2,400 | 1 Day (7–8 hrs), Physical |
| **Tier 3: Block** | BDOs, Block IT Staff, Extension Officers, Welfare Supervisors, DEOs | 314 | ~30 | 9,420 | 1 Day (7–8 hrs), Physical |
| **Tier 4: Gram Panchayat** | Panchayat Executive Officers (PEOs), Welfare Assistants, Frontline Data Handlers | 1,675 | ~16–29 (Cluster/Individual) | 26,800 | 1/2 Day (3–4 hrs), Physical / Cluster |
| **Awareness: Citizens** | SHGs, Youth Groups, Students, Community Leaders | ~50 | ~50 | 2,500+ | 1–2 Hours, Community Sessions |
| **Total** | | **~2,199** | | **~44,120+** | |

---

## 3. Core Functional Requirements

### 3.1 Master Data Management
- Maintain 30 Districts of Odisha, 314 Blocks, and associated Gram Panchayats.
- Dynamic cascading filters: selecting a District loads its Blocks; selecting a Block loads its Gram Panchayats.
- Support cluster batches covering multiple Gram Panchayats in a single session.
- Manage standard Training Venues with capacity, contact details, and facilities.
- Maintain Master Trainers accredited through Directorate-level Training of Trainers (ToT) programs (target $\ge 250$ Master Trainers).
- Manage standard Curricular Modules (Modules 1 to 8):
  - *Module 1: Introduction to OSDP 2.0*
  - *Module 2: Data Classification & Categorisation*
  - *Module 3: Data Lifecycle Management*
  - *Module 4: Data Privacy & Security Practices*
  - *Module 5: Data Quality Management*
  - *Module 6: Inter-Departmental Data Sharing*
  - *Module 7: Documentation, Registers & Reporting*
  - *Module 8: Case Studies & Practical Exercises*

### 3.2 Training Planning & Batch Scheduling
- Unique auto-generated Batch Code formatted deterministically (e.g., `OSDP-DIR-2026-0001`, `OSDP-DST-KHD-0012`, `OSDP-BLK-KAN-0045`, `OSDP-GP-PAT-0120`).
- Capture: Training Plan, Training Tier, District, Block (nullable for Directorate/District), Gram Panchayat(s) (cluster support), Venue, Scheduled Date, Planned Participant Count, Assigned Primary & Co-Trainers.
- Real-time conflict checking (prevent double-booking venues or trainers on the same date/time).
- Batch status state machine with rigorous transition policies:
  `planned` $\rightarrow$ `scheduled` $\rightarrow$ `in_progress` $\rightarrow$ `awaiting_evidence` $\rightarrow$ `submitted` $\rightarrow$ `under_review` $\rightarrow$ `verified` (or `correction_required` / `rejected`) $\rightarrow$ `closed`.

### 3.3 Participant & Attendance Management (Form 2)
- Record participant details without retaining sensitive personal identification numbers (DPDP compliance):
  - Participant Name, Designation, Department / Office / GP, Contact Number (optional/masked), Email (optional).
- Digital transcription of signed paper attendance sheets (Form 2):
  - Morning Attendance (sign-in)
  - Afternoon Attendance (post-lunch sign-in; omitted for half-day GP batches)
- Automatic aggregation of Registered vs. Present participant counts.
- Batch attendance sheet metadata (trainer certification, DC verification signatures).

### 3.4 Evidence Pack Management (MIS Upload Checklist & Form 3)
Every completed batch requires an evidence pack containing six mandatory items:
1. **Attendance Sheet (Form 2):** High-resolution scans/photos of both sides (2 images or 1 merged PDF).
2. **Photographs (2 JPEGs):**
   - Photo 1: Wide-angle view showing all participants, trainer, and mandatory 8ft $\times$ 5ft banner.
   - Photo 2: Trainer actively instructing the session.
   - *Strict privacy rule:* No photos of registers or screens containing beneficiary personal data.
3. **Short Video (1 MP4 clip):** 30–120 seconds (nominally ~1 min), widescreen landscape orientation, panning room, audio clear.
4. **Feedback Forms (Form 1):** Scanned PDF bundle of all participant feedback forms.
5. **Training Summary (Form 3, Part C):** Structured data (actual timings, pre/post assessment done, highlights, problems faced, follow-up actions).
6. **Batch Completion Report (Form 3 PDF):** Official signed single-page completion report.

#### Upload Rules & Constraints:
- 48-hour submission SLA calculated from batch completion timestamp.
- Private filesystem storage; direct public URL access strictly prohibited.
- File integrity checks: server-side MIME type verification, extension whitelisting, randomized SHA/UUID file storage names, file size limits (configurable: images $\le 10$ MB, PDF $\le 25$ MB, MP4 $\le 100$ MB).

### 3.5 Review, Verification & Workflow Engine
- **District Coordinator (DC) Initial Review:** Checks physical pack completeness, validates legible scans, marks "Pack Checked".
- **PMU Review:** Central verification of evidence items, feedback score averages, curriculum delivery, and operational compliance.
- **Decision Engine:**
  - `Accept / Verify`: Marks batch as verified, locks records from further edits, flags ready for milestone payment.
  - `Request Correction`: Requires resubmission with specific itemized feedback notes.
  - `Reject`: Flags severe non-compliance.
- **Audit History:** Full immutable trail of review decisions, timestamps, reviewer remarks, and previous evidence versions.
- **Independent Field Monitoring:** On-site inspection records logged by third-party field monitors (physical presence, attendance headcount verification, training kit distribution, venue adequacy).

### 3.6 Dashboards & Analytics
- **Role-Scoped Dashboards:**
  - *Super Admin:* System health, active users, storage utilization, security logs.
  - *OCAC:* Statewide executive monitoring, district completion matrix, tier-wise reach, feedback quality ratings, real-time heatmaps (read-only audit view).
  - *PMU:* Operational control center, review queues, overdue submissions ($>48$ hrs), trainer performance, batch status breakdown.
  - *District Coordinator:* District-specific batch calendar, pending uploads, missing attendance sheets, incomplete evidence alerts.
  - *Field Trainer:* Assigned upcoming batches, upload status, resubmission notices, personal completion scorecard.
  - *Independent Field Monitor:* Assigned visit schedule, pending verification reports, deviation logs.
- Interactive Chart.js charts: Batch completion trends, Tier distribution, District compliance radar, Feedback rating averages.

### 3.7 Reports & Regulatory Exports
- Dynamic filtering (Tier, District, Block, GP, Trainer, Date Range, Status).
- Export formats: CSV, Excel, and printable PDF.
- Standard Reports:
  1. Statewide Training Progress Report
  2. District & Block Performance Summary
  3. Batch Completion & Evidence Audit Report
  4. Participant Attendance Master Register
  5. Overdue / Defaulter Batch Report
  6. Feedback & Quality Rating Evaluation
  7. Field Verification Summary Report
  8. Security & System Audit Trail Report

---

## 4. Security, Privacy & Compliance (DPDP Act 2023 & OSDC)

1. **Hosting Environment:** Prepared for Odisha State Data Centre (OSDC) Linux/MySQL/Nginx/Apache environment.
2. **Data Minimization:** No Aadhaar numbers, bank account numbers, or sensitive citizen welfare identifiers stored in MIS.
3. **Private File Storage:** Evidence documents stored outside the web root (`storage/app/private/evidence/...`) served only via authenticated streaming controllers with authorization policies.
4. **Access Control:** Spatie Role-Based Access Control coupled with Laravel Model Policies enforcing geographic and organizational boundaries.
5. **Session Security & Brute Force Protection:** Rate-limited authentication (5 attempts / min), session regeneration on login, CSRF tokens on all mutations, HTTP-only secure cookies.
6. **Immutable Audit Logging:** Every create, update, delete, review decision, and file access event recorded with actor ID, IP address, user agent, old values, and new values.

---

## 5. Items Requiring OCAC Clarification

The following items represent operational assumptions derived from the proposal documents and require formal sign-off during inception:
1. **Upload Responsibility Split:** Whether Field Trainers or District Coordinators perform the primary upload of Form 1 feedback scans and Form 2 attendance sheets (system currently supports both based on role delegation).
2. **Video Duration & Size Cap:** RFP specifies ~1 min MP4 clip; server limit tentatively set to 100 MB per batch.
3. **Paper Pack Retention SLA:** Operational guidelines suggest 3 to 5 years retention at District / OCAC archives.
4. **Bilingual Odia Localization:** English system default with localized Odia report headers; full bilingual UI strings pending OCAC-approved Odia lexicon.
