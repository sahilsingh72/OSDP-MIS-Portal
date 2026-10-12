# OSDP MIS Portal - Role & Permission Matrix
**Odisha State Data Policy 2.0 Management Information System**

---

## 1. User Roles

1. **Super Admin (`super-admin`):**
   - System administrator and technical custodian.
   - Manages user accounts, role assignments, system configurations, and security audit logs.
2. **OCAC (`ocac`):**
   - Odisha Computer Application Centre nodal authority.
   - Statewide read-only executive monitoring, progress oversight, policy compliance reviews, and random sample audits.
3. **PMU / Programme Manager (`pmu`):**
   - Central Programme Management Unit responsible for operational delivery.
   - Statewide scheduling oversight, batch verification decisions, resubmission tracking, and vendor milestone validation.
4. **District Coordinator (`district-coordinator`):**
   - District-level officer managing training operations in assigned district(s).
   - Verifies physical packs, validates and uploads Form 2 & Form 1 scans, tracks 48-hour submission deadlines.
5. **Field Trainer (`field-trainer`):**
   - Accredited master trainer assigned to conduct training batches.
   - Enters participant lists, records attendance, inputs Form 3 summary, uploads session photos and video clip.
6. **Independent Field Monitor (`field-monitor`):**
   - Third-party quality auditor visiting Block and GP training batches.
   - Logs independent on-site verification reports, validates headcount, kit distribution, and banner display.

---

## 2. Granular Permissions Catalog

| Permission Key | Description |
|---|---|
| `users.view` | View user profiles and directory |
| `users.create` | Register new staff and field users |
| `users.update` | Edit user profile and assignments |
| `users.deactivate` | Deactivate/suspend user access |
| `roles.manage` | Configure roles and permission bindings |
| `master-data.view` | View districts, blocks, GPs, venues, modules |
| `master-data.manage` | Add or modify master data reference items |
| `training-plans.view` | View master training plans and targets |
| `training-plans.create` | Author and publish new training plans |
| `training-plans.update` | Modify existing training plans |
| `batches.view` | View training batch schedules and details |
| `batches.create` | Schedule new training batches |
| `batches.update` | Update scheduled batch venue, dates, timings |
| `batches.assign` | Assign trainers and coordinators to batches |
| `participants.view` | View participant attendance and rosters |
| `participants.manage` | Add, import, or update batch participants |
| `attendance.view` | View digital attendance sheets (Form 2) |
| `attendance.manage` | Transcribe and sign off on morning/afternoon attendance |
| `evidence.view` | View uploaded evidence list and metadata |
| `evidence.upload` | Upload attendance, photos, video, feedback, Form 3 |
| `evidence.download` | Download/stream private evidence files |
| `evidence.review` | Complete District Coordinator physical completeness check |
| `reviews.manage` | Execute PMU approval, rejection, or correction request |
| `field-verifications.view` | View independent monitoring audit reports |
| `field-verifications.create` | Submit on-site field verification assessment |
| `reports.view` | View analytics tables and dashboard metrics |
| `reports.export` | Export CSV, Excel, and PDF monitoring reports |
| `audit-logs.view` | Inspect system change history and security events |
| `settings.manage` | Configure SLAs, size caps, and operational parameters |

---

## 3. Role vs Permission Matrix

| Permission | Super Admin | OCAC | PMU | District Coordinator | Field Trainer | Field Monitor |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| `users.view` | ✅ | ✅ | ✅ | ✅ (District) | ❌ | ❌ |
| `users.create` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `users.update` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `users.deactivate` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `roles.manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `master-data.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `master-data.manage` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `training-plans.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `training-plans.create` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `training-plans.update` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `batches.view` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `batches.create` | ✅ | ❌ | ✅ | ✅ (District) | ❌ | ❌ |
| `batches.update` | ✅ | ❌ | ✅ | ✅ (District) | ✅ (Own Draft) | ❌ |
| `batches.assign` | ✅ | ❌ | ✅ | ✅ (District) | ❌ | ❌ |
| `participants.view` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `participants.manage`| ✅ | ❌ | ✅ | ✅ (District) | ✅ (Assigned) | ❌ |
| `attendance.view` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `attendance.manage` | ✅ | ❌ | ✅ | ✅ (District) | ✅ (Assigned) | ❌ |
| `evidence.view` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `evidence.upload` | ✅ | ❌ | ✅ | ✅ (District) | ✅ (Assigned) | ❌ |
| `evidence.download` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `evidence.review` | ✅ | ❌ | ✅ | ✅ (District) | ❌ | ❌ |
| `reviews.manage` | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `field-verifications.view` | ✅ | ✅ | ✅ | ✅ (District) | ❌ | ✅ (Assigned) |
| `field-verifications.create`| ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `reports.view` | ✅ | ✅ | ✅ | ✅ (District) | ✅ (Assigned) | ✅ (Assigned) |
| `reports.export` | ✅ | ✅ | ✅ | ✅ (District) | ❌ | ❌ |
| `audit-logs.view` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `settings.manage` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 4. Record-Level Data Scoping (Model Policies)

In addition to RBAC role checks, data filters are enforced in Eloquent queries and Laravel Model Policies:

1. **Super Admin, OCAC & PMU:**
   - Unrestricted geographic scope across all 30 districts and all 2,149+ batches.
   - OCAC is enforced as read-only across operational mutations.
2. **District Coordinator:**
   - Scoped strictly to batches and users where `batch.district_id IN (user.assigned_districts)`.
   - Prevented from scheduling or uploading for unauthorized districts even if manipulating request payloads.
3. **Field Trainer:**
   - Scoped strictly to batches where `batch.id IN (SELECT batch_id FROM batch_trainers WHERE user_id = auth.id)`.
   - Cannot view or upload evidence for batches led by other trainers.
4. **Independent Field Monitor:**
   - Scoped to batches explicitly assigned for on-site monitoring visits.
